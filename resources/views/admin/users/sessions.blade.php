@extends('layouts.admin')
@section('title', 'Device & Session Audit')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('admin.users.index') }}" class="text-xs font-semibold text-blue-600 hover:underline">&larr; Back to Users</a>
            </div>
            <h1 class="text-2xl font-bold text-slate-900">Device & Login Audit: {{ $user->name }}</h1>
            <p class="text-slate-500 text-sm mt-0.5">{{ $user->email }} @if($user->phone)&bull; {{ $user->phone }}@endif</p>
        </div>

        @if(!empty($user->active_session_token))
            <form method="POST" action="{{ route('admin.users.force-logout', $user->id) }}" onsubmit="return confirm('Force terminate this session now?');">
                @csrf
                <button type="submit" class="inline-flex items-center gap-2 bg-rose-600 hover:bg-rose-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl shadow-sm transition">
                    <i data-lucide="shield-alert" class="w-4 h-4"></i> Force Terminate Active Session
                </button>
            </form>
        @endif
    </div>

    <!-- Active Device Card -->
    <div class="bg-gradient-to-br from-slate-900 to-indigo-950 text-white p-6 rounded-2xl shadow-md border border-slate-800">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <span class="text-xs uppercase font-bold tracking-wider text-sky-400">Current Single-Device Lock Status</span>
                <div class="flex items-center gap-3 mt-2">
                    @if(!empty($user->active_session_token))
                        <div class="w-3 h-3 rounded-full bg-emerald-400 animate-ping"></div>
                        <h3 class="text-lg font-bold text-white">Active Device Session Connected</h3>
                    @else
                        <div class="w-3 h-3 rounded-full bg-slate-500"></div>
                        <h3 class="text-lg font-bold text-slate-300">No Device Currently Connected</h3>
                    @endif
                </div>
                <p class="text-xs text-slate-400 mt-1">
                    {{ $user->active_device_label ?? 'User is not currently logged into any device' }}
                </p>
            </div>
            <div class="flex flex-wrap gap-4 text-xs">
                <div class="bg-white/10 rounded-xl px-4 py-2.5 backdrop-blur-sm">
                    <div class="text-slate-400">Membership Expiry</div>
                    <div class="font-bold text-white mt-0.5">
                        {{ $user->membership_expires_at ? $user->membership_expires_at->format('M d, Y') : 'Active' }}
                        @if($user->isMembershipActive())
                            <span class="text-emerald-400 font-normal">({{ $user->remainingMembershipDays() }}d left)</span>
                        @else
                            <span class="text-rose-400 font-normal">(Expired)</span>
                        @endif
                    </div>
                </div>
                <div class="bg-white/10 rounded-xl px-4 py-2.5 backdrop-blur-sm">
                    <div class="text-slate-400">Total Logins</div>
                    <div class="font-bold text-white mt-0.5">{{ $user->login_count }} sessions</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sessions Audit Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-bold text-slate-800 text-sm">Session History & Kickout Log</h3>
            <span class="text-xs text-slate-400">Audit trail of all logins across PCs, Laptops, and Mobiles</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3">State</th>
                        <th class="px-6 py-3">Device / Platform</th>
                        <th class="px-6 py-3">IP Address</th>
                        <th class="px-6 py-3">Logged In</th>
                        <th class="px-6 py-3">Logged Out</th>
                        <th class="px-6 py-3">Termination Reason</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($sessions as $s)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="px-6 py-3.5">
                                @if($s->is_active)
                                    <span class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-full">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> Active
                                    </span>
                                @else
                                    <span class="text-xs text-slate-400 font-medium">Terminated</span>
                                @endif
                            </td>
                            <td class="px-6 py-3.5">
                                <div class="font-medium text-slate-800 text-xs">{{ $s->device_type ?? 'Web Device' }}</div>
                                <div class="text-[11px] text-slate-400 truncate max-w-[220px]" title="{{ $s->user_agent }}">{{ $s->browser ?? $s->user_agent }}</div>
                            </td>
                            <td class="px-6 py-3.5 font-mono text-xs text-slate-600">
                                {{ $s->ip_address ?? '—' }}
                            </td>
                            <td class="px-6 py-3.5 text-xs text-slate-500">
                                {{ $s->logged_in_at ? $s->logged_in_at->format('M d, Y h:i A') : '—' }}
                            </td>
                            <td class="px-6 py-3.5 text-xs text-slate-500">
                                {{ $s->logged_out_at ? $s->logged_out_at->format('M d, Y h:i A') : ($s->is_active ? 'Currently active' : '—') }}
                            </td>
                            <td class="px-6 py-3.5">
                                @if($s->logout_reason === 'forced_by_new_login')
                                    <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-amber-800 bg-amber-50 px-2.5 py-1 rounded-lg">
                                        <i data-lucide="shuffle" class="w-3 h-3 text-amber-600"></i> Kicked by New Login (PC-2)
                                    </span>
                                @elseif($s->logout_reason === 'admin_forced')
                                    <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-rose-800 bg-rose-50 px-2.5 py-1 rounded-lg">
                                        <i data-lucide="shield-x" class="w-3 h-3 text-rose-600"></i> Admin Force Logout
                                    </span>
                                @elseif($s->logout_reason === 'membership_expired')
                                    <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-purple-800 bg-purple-50 px-2.5 py-1 rounded-lg">
                                        <i data-lucide="clock" class="w-3 h-3 text-purple-600"></i> Membership Expired
                                    </span>
                                @elseif($s->logout_reason === 'manual_logout')
                                    <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-blue-800 bg-blue-50 px-2.5 py-1 rounded-lg">
                                        <i data-lucide="log-out" class="w-3 h-3 text-blue-600"></i> Manual Logout
                                    </span>
                                @else
                                    <span class="text-xs text-slate-400">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                                <i data-lucide="shield-off" class="w-8 h-8 mx-auto text-slate-300 mb-2"></i>
                                <p class="text-xs font-medium">No session history found for this user.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($sessions->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $sessions->links() }}
            </div>
        @endif
    </div>
</div>
@endsection