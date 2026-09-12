<?php

namespace App\Services;

use App\Models\CreditWallet;
use App\Models\CreditTransaction;
use App\Exceptions\InsufficientCreditsException;
use Illuminate\Support\Facades\DB;

class CreditService
{
    public function deduct(CreditWallet $wallet, int $amount, string $type, ?string $referenceType = null, ?int $referenceId = null, ?string $description = null, ?string $idempotencyKey = null, ?int $userId = null): CreditTransaction
    {
        return DB::transaction(function () use ($wallet, $amount, $type, $referenceType, $referenceId, $description, $idempotencyKey, $userId) {
            $lockedWallet = CreditWallet::withoutGlobalScopes()
                ->where('id', $wallet->id)
                ->lockForUpdate()
                ->first();

            if (!$lockedWallet || $lockedWallet->balance < $amount) {
                throw new InsufficientCreditsException();
            }

            $balanceBefore = $lockedWallet->balance;
            $lockedWallet->balance -= $amount;
            $lockedWallet->total_spent += $amount;
            $lockedWallet->save();

            return CreditTransaction::create([
                'tenant_id' => $lockedWallet->tenant_id,
                'user_id' => $userId,
                'wallet_id' => $lockedWallet->id,
                'type' => $type,
                'credits' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $lockedWallet->balance,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'description' => $description,
                'idempotency_key' => $idempotencyKey,
            ]);
        });
    }

    public function add(CreditWallet $wallet, int $amount, string $type, ?string $referenceType = null, ?int $referenceId = null, ?string $description = null, ?int $userId = null): CreditTransaction
    {
        return DB::transaction(function () use ($wallet, $amount, $type, $referenceType, $referenceId, $description, $userId) {
            $lockedWallet = CreditWallet::withoutGlobalScopes()
                ->where('id', $wallet->id)
                ->lockForUpdate()
                ->first();

            if (!$lockedWallet) {
                throw new \RuntimeException('Credit wallet not found.');
            }

            $balanceBefore = $lockedWallet->balance;
            $lockedWallet->balance += $amount;
            $lockedWallet->total_earned += $amount;
            $lockedWallet->save();

            return CreditTransaction::create([
                'tenant_id' => $lockedWallet->tenant_id,
                'user_id' => $userId,
                'wallet_id' => $lockedWallet->id,
                'type' => $type,
                'credits' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $lockedWallet->balance,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'description' => $description,
            ]);
        });
    }

    public function getBalance(int $tenantId, ?int $userId = null): int
    {
        $wallet = $this->getOrCreateWallet($tenantId, $userId);
        return $wallet->balance;
    }

    public function getOrCreateWallet(int $tenantId, ?int $userId = null): CreditWallet
    {
        return CreditWallet::withoutGlobalScopes()->firstOrCreate([
            'tenant_id' => $tenantId,
            'user_id' => $userId,
        ]);
    }

    /* ── Convenience methods used by controllers ── */

    public function getWallet(int $tenantId): ?CreditWallet
    {
        return CreditWallet::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->whereNull('user_id')
            ->first();
    }

    public function checkBalance(int $tenantId, int $requiredCredits): bool
    {
        $wallet = $this->getWallet($tenantId);
        return $wallet && $wallet->balance >= $requiredCredits;
    }

    public function deductCredits(int $tenantId, int $userId, int $amount, string $type, ?string $description = null, $reference = null): CreditTransaction
    {
        $wallet = $this->getOrCreateWallet($tenantId, null);

        return $this->deduct(
            $wallet,
            $amount,
            $type,
            $reference ? get_class($reference) : null,
            $reference?->id,
            $description,
            null,
            $userId
        );
    }

    public function addCredits(int $tenantId, int $amount, string $type, ?string $description = null, $reference = null, ?int $userId = null): CreditTransaction
    {
        $wallet = $this->getOrCreateWallet($tenantId, null);

        return $this->add(
            $wallet,
            $amount,
            $type,
            $reference ? get_class($reference) : null,
            $reference?->id,
            $description,
            $userId
        );
    }

    public function getTransactions(int $tenantId, ?string $type = null, int $perPage = 20)
    {
        $query = CreditTransaction::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->when($type, fn($q, $v) => $q->where('type', $v))
            ->orderByDesc('created_at');

        return $query->paginate($perPage);
    }
}
