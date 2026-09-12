<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\Subscription;
use App\Models\Plan;
use App\Models\User;

class SubscriptionService
{
    public function subscribe(
        Tenant $tenant,
        $plan,
        $payment = null,
        ?int $userId = null,
        ?int $durationDays = null,
        ?int $dataLimitTotal = null
    ): Subscription {
        // Cancel any existing active subscription for this tenant / user
        $cancelQuery = Subscription::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->where('status', 'active');

        if ($userId) {
            $cancelQuery->where(function ($q) use ($userId) {
                $q->where('user_id', $userId)->orWhereNull('user_id');
            });
        }

        $cancelQuery->update(['status' => 'cancelled', 'cancelled_at' => now()]);

        // Calculate duration and expiration
        $effectiveDays = $durationDays ?: ($plan->duration_days ?: null);

        if ($effectiveDays) {
            $expiresAt = now()->addDays($effectiveDays);
        } else {
            $expiresAt = match ($plan->billing_cycle) {
                'monthly' => now()->addMonth(),
                'yearly' => now()->addYear(),
                'one_time' => now()->addYear(),
                'custom' => now()->addDays(28),
                default => now()->addMonth(),
            };
        }

        $limitTotal = $dataLimitTotal ?? ($plan->max_data_units_total ?? null);

        $subscription = Subscription::create([
            'tenant_id' => $tenant->id,
            'user_id' => $userId,
            'plan_id' => $plan->id,
            'duration_days' => $effectiveDays,
            'data_limit_total' => $limitTotal,
            'data_used_total' => 0,
            'status' => $payment ? 'active' : 'trial',
            'starts_at' => now(),
            'expires_at' => $expiresAt,
            'payment_id' => $payment?->id,
            'auto_renew' => true,
        ]);

        // Sync membership_expires_at on user
        if ($userId) {
            User::where('id', $userId)->update(['membership_expires_at' => $expiresAt]);
        } else {
            User::where('tenant_id', $tenant->id)->update(['membership_expires_at' => $expiresAt]);
        }

        // Add plan credits
        if ($plan->credits_included > 0) {
            app(CreditService::class)->addCredits(
                $tenant->id,
                $plan->credits_included,
                'subscription',
                'Plan credits: ' . $plan->name,
                $subscription
            );
        }

        // Update tenant status
        $tenant->update(['status' => 'active']);

        return $subscription;
    }

    public function getCurrentSubscription(Tenant $tenant): ?Subscription
    {
        return Subscription::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->whereIn('status', ['active', 'trial'])
            ->with('plan')
            ->latest('starts_at')
            ->first();
    }

    public function isActive(int $tenantId): bool
    {
        return Subscription::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->whereIn('status', ['active', 'trial'])
            ->where('expires_at', '>', now())
            ->exists();
    }

    public function cancelSubscription(Subscription $subscription): void
    {
        $subscription->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'auto_renew' => false,
        ]);
    }

    public function handleExpiry(): int
    {
        $expired = Subscription::withoutGlobalScopes()
            ->whereIn('status', ['active', 'trial'])
            ->where('expires_at', '<=', now())
            ->get();

        foreach ($expired as $sub) {
            $sub->update(['status' => 'expired']);

            // Notify tenant owner
            $owner = $sub->tenant?->users()
                ->whereHas('roles', fn($q) => $q->where('name', 'tenant_owner'))
                ->first();

            if ($owner) {
                try {
                    $owner->notify(new \App\Notifications\SubscriptionExpiringNotification($sub));
                } catch (\Throwable $e) {}
            }
        }

        return $expired->count();
    }
}