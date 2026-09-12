<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Subscription;
use App\Models\UserLoginSession;

class CheckMembershipExpiry extends Command
{
    protected $signature = 'doxa:check-membership-expiry';
    protected $description = 'Check and auto-expire memberships and terminate active device sessions';

    public function handle(): int
    {
        $this->info('Checking membership expiries...');

        // 1. Find users whose membership has expired
        $expiredUsers = User::withoutGlobalScopes()
            ->where('is_super_admin', false)
            ->whereNotNull('membership_expires_at')
            ->where('membership_expires_at', '<', now())
            ->where('status', 'active')
            ->get();

        $userCount = 0;
        foreach ($expiredUsers as $user) {
            $user->update([
                'status' => 'suspended',
                'active_session_token' => null,
            ]);

            // Invalidate active device sessions
            UserLoginSession::where('user_id', $user->id)
                ->where('is_active', true)
                ->update([
                    'is_active' => false,
                    'logged_out_at' => now(),
                    'logout_reason' => 'membership_expired',
                ]);

            // Revoke personal access tokens
            try {
                $user->tokens()->delete();
            } catch (\Throwable $e) {}

            $userCount++;
        }

        // 2. Mark subscriptions expired
        $expiredSubs = Subscription::withoutGlobalScopes()
            ->where('status', 'active')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->update(['status' => 'expired']);

        $this->info("Expired {$userCount} users and {$expiredSubs} subscriptions.");

        return Command::SUCCESS;
    }
}