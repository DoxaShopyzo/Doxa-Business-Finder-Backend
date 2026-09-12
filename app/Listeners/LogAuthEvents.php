<?php

namespace App\Listeners;

use App\Models\LoginLog;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Failed;
use Illuminate\Http\Request;

class LogAuthEvents
{
    public function __construct(protected Request $request) {}

    public function handleLogin(Login $event): void
    {
        LoginLog::create([
            'user_id' => $event->user->id,
            'ip_address' => $this->request->ip(),
            'user_agent' => $this->request->userAgent(),
            'device' => $this->parseDevice($this->request->userAgent()),
            'status' => 'success',
        ]);

        $event->user->update([
            'last_login_at' => now(),
            'login_count' => ($event->user->login_count ?? 0) + 1,
        ]);
    }

    public function handleFailed(Failed $event): void
    {
        if ($event->user) {
            LoginLog::create([
                'user_id' => $event->user->id,
                'ip_address' => $this->request->ip(),
                'user_agent' => $this->request->userAgent(),
                'device' => $this->parseDevice($this->request->userAgent()),
                'status' => 'failed',
            ]);
        }
    }

    protected function parseDevice(?string $userAgent): ?string
    {
        if (!$userAgent) return null;
        if (str_contains($userAgent, 'Dart')) return 'Flutter App';
        if (str_contains($userAgent, 'Mobile')) return 'Mobile Browser';
        if (str_contains($userAgent, 'Chrome')) return 'Chrome';
        if (str_contains($userAgent, 'Firefox')) return 'Firefox';
        return 'Other';
    }
}
