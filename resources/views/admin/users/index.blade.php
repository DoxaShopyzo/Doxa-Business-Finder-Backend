@extends('layouts.admin')
@section('title', 'User & Membership Management')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Users & Device Memberships</h1>
            <p class="text-slate-500 text-sm mt-1">Manage single-device logins, duration expiries (28/84 days), and total data collection caps</p>
        </div>
        <a href="{{ route('admin.users.create') }}" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl shadow-sm transition">
            <i data-lucide="user-plus" class="w-4 h-4"></i> Create Membership User
        </a>
    </div>

    <!-- Filters & Stats -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
        <form method="GET" action="{{ route('admin.users.index') }}" class="w-full md:w-auto flex-1 flex flex-wrap items-center gap-3">
            <div class="relative flex-1 min-w-[240px]">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                    <i data-lucide="search" class="w-4 h-4"></i>
                </span>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, email, or mobile..."
                    class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-9 pr-4 py-2 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition">
            </div>

            <select name="membership_status" class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-blue-500">
                <option value="">All Memberships</option>
                <option value="active" {{ request('membership_status') === 'active' ? 'selected' : '' }}>Active Membership</option>
                <option value="expired" {{ request('membership_status') === 'expired' ? 'selected' : '' }}>Expired Membership</option>
            </select>

            <button type="submit" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-4 py-2 rounded-xl text-sm font-medium transition">Filter</button>
            @if(request()->hasAny(['search', 'membership_status']))
                <a href="{{ route('admin.users.index') }}" class="text-xs text-slate-500 hover:text-slate-800">Clear</a>
            @endif
        </form>
        <span class="text-xs text-slate-500 font-medium">Total: {{ $users->total() }} accounts</span>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50/75 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4">User / Contact</th>
                        <th class="px-6 py-4">Membership Duration</th>
                        <th class="px-6 py-4">Data Collection Quota</th>
                        <th class="px-6 py-4">Active Device Lock</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($users as $user)
                        @php
                            $sub = $user->active_subscription;
                            $isActive = $user->isMembershipActive();
                            $daysLeft = $user->remainingMembershipDays();
                            $hasActiveSession = !empty($user->active_session_token);
                            $usedData = $sub?->data_used_total ?? 0;
                            $limitData = $sub?->data_limit_total;
                            $percentUsed = ($limitData && $limitData > 0) ? min(100, (int) round(($usedData / $limitData) * 100)) : 0;
                        @endphp
                        <tr class="hover:bg-slate-50/50 transition {{ !$isActive ? 'bg-slate-50/30' : '' }}">
                            <!-- User info -->
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-blue-500 to-indigo-600 text-white font-bold flex items-center justify-center text-sm shadow-sm flex-shrink-0">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="font-semibold text-slate-900 flex items-center gap-2">
                                            {{ $user->name }}
                                            @if($user->tenant)
                                                <span class="text-[10px] bg-slate-100 text-slate-600 px-2 py-0.5 rounded font-normal">{{ $user->tenant->company_name ?? $user->tenant->name }}</span>
                                            @endif
                                        </div>
                                        <div class="text-xs text-slate-500">{{ $user->email }}</div>
                                        @if($user->phone)
                                            <div class="text-[11px] text-slate-400 font-mono mt-0.5"><i data-lucide="phone" class="w-3 h-3 inline mr-1"></i>{{ $user->phone }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <!-- Membership Duration Status -->
                            <td class="px-6 py-4">
                                @if($isActive)
                                    @if($daysLeft <= 3)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                                            <span class="w-2 h-2 rounded-full bg-amber-500"></span> Expiring in {{ $daysLeft }} {{ Str::plural('day', $daysLeft) }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Active ({{ $daysLeft }}d left)
                                        </span>
                                    @endif
                                    <div class="text-[11px] text-slate-400 mt-1">
                                        Expires: {{ $user->membership_expires_at ? $user->membership_expires_at->format('M d, Y') : ($sub?->expires_at ? $sub->expires_at->format('M d, Y') : 'Active') }}
                                    </div>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-100 text-rose-700">
                                        <span class="w-2 h-2 rounded-full bg-rose-500"></span> Membership Expired
                                    </span>
                                    @if($user->membership_expires_at)
                                        <div class="text-[11px] text-slate-400 mt-1">Ended: {{ $user->membership_expires_at->format('M d, Y') }}</div>
                                    @endif
                                @endif
                            </td>

                            <!-- Data Collection Quota -->
                            <td class="px-6 py-4">
                                @if($limitData)
                                    <div class="w-44">
                                        <div class="flex justify-between text-xs mb-1">
                                            <span class="font-medium text-slate-700">{{ number_format($usedData) }} / {{ number_format($limitData) }}</span>
                                            <span class="font-semibold {{ $percentUsed >= 95 ? 'text-rose-600' : ($percentUsed >= 70 ? 'text-amber-600' : 'text-slate-600') }}">{{ $percentUsed }}%</span>
                                        </div>
                                        <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                                            <div class="h-2 rounded-full transition-all duration-300 {{ $percentUsed >= 95 ? 'bg-rose-500' : ($percentUsed >= 70 ? 'bg-amber-500' : 'bg-blue-600') }}"
                                                style="width: {{ $percentUsed }}%"></div>
                                        </div>
                                        <div class="mt-1 flex items-center justify-between text-[11px]">
                                            <span class="text-slate-400">{{ max(0, $limitData - $usedData) }} left</span>
                                            <!-- Top-up button trigger -->
                                            <button type="button" onclick="openTopupModal('{{ $user->id }}', '{{ addslashes($user->name) }}', {{ $limitData }})" class="text-blue-600 hover:text-blue-800 font-semibold">
                                                + Top-up
                                            </button>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-xs text-slate-400 italic">No limit cap</span>
                                @endif
                            </td>

                            <!-- Active Device Lock -->
                            <td class="px-6 py-4">
                                @if($hasActiveSession)
                                    <div class="flex items-start gap-2">
                                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 mt-1 flex-shrink-0 animate-pulse"></span>
                                        <div>
                                            <div class="text-xs font-semibold text-slate-800 flex items-center gap-1">
                                                <i data-lucide="shield-check" class="w-3.5 h-3.5 text-emerald-600"></i> Locked to 1 Device
                                            </div>
                                            <div class="text-[11px] text-slate-500 truncate max-w-[180px]" title="{{ $user->active_device_label }}">
                                                {{ $user->active_device_label ?? 'Active Session' }}
                                            </div>
                                            <div class="text-[10px] text-slate-400">
                                                Started: {{ $user->session_started_at ? $user->session_started_at->diffForHumans() : 'Recently' }}
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-xs text-slate-400 italic flex items-center gap-1">
                                        <i data-lucide="shield-off" class="w-3.5 h-3.5"></i> No active session
                                    </span>
                                @endif
                            </td>

                            <!-- Actions -->
                            <td class="px-6 py-4 text-right space-y-1">
                                <div class="flex items-center justify-end gap-1.5">
                                    @if($hasActiveSession)
                                        <form method="POST" action="{{ route('admin.users.force-logout', $user->id) }}" onsubmit="return confirm('Force logout {{ addslashes($user->name) }}? This will instantly terminate their active session on their device.');">
                                            @csrf
                                            <button type="submit" title="Force Logout Session" class="inline-flex items-center gap-1 text-[11px] font-semibold text-rose-600 hover:text-rose-800 bg-rose-50 hover:bg-rose-100 px-2.5 py-1.5 rounded-lg transition">
                                                <i data-lucide="log-out" class="w-3.5 h-3.5"></i> Kick
                                            </button>
                                        </form>
                                    @endif

                                    <a href="{{ route('admin.users.sessions', $user->id) }}" title="Device Audit History" class="inline-flex items-center gap-1 text-[11px] font-semibold text-slate-600 hover:text-slate-800 bg-slate-100 hover:bg-slate-200 px-2.5 py-1.5 rounded-lg transition">
                                        <i data-lucide="history" class="w-3.5 h-3.5"></i> Devices
                                    </a>

                                    <a href="{{ route('admin.users.edit', $user->id) }}" title="Edit Account" class="inline-flex items-center gap-1 text-[11px] font-semibold text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 px-2.5 py-1.5 rounded-lg transition">
                                        <i data-lucide="edit-2" class="w-3.5 h-3.5"></i> Edit
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-400">
                                <i data-lucide="users" class="w-10 h-10 mx-auto text-slate-300 mb-2"></i>
                                <p class="font-medium">No users found.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal: Top-up Quota -->
<div id="topupModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl p-6 max-w-md w-full shadow-2xl border border-slate-100">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-bold text-slate-900 text-lg">Top-up Data Quota</h3>
            <button onclick="closeTopupModal()" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>
        <p class="text-xs text-slate-500 mb-4">Add extra business record allowance for <span id="topupUserName" class="font-semibold text-slate-800"></span> without changing their membership duration.</p>
        <form id="topupForm" method="POST" action="">
            @csrf
            <div class="mb-4">
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Add Records to Quota</label>
                <div class="grid grid-cols-3 gap-2 mb-2">
                    <button type="button" onclick="setTopupAmount(500)" class="py-1.5 text-xs font-medium border border-slate-200 rounded-lg hover:bg-slate-50">+500</button>
                    <button type="button" onclick="setTopupAmount(1000)" class="py-1.5 text-xs font-medium border border-slate-200 rounded-lg hover:bg-slate-50">+1,000</button>
                    <button type="button" onclick="setTopupAmount(5000)" class="py-1.5 text-xs font-medium border border-slate-200 rounded-lg hover:bg-slate-50">+5,000</button>
                </div>
                <input type="number" id="extraUnitsInput" name="extra_units" min="1" max="500000" value="1000" required
                    class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition">
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" onclick="closeTopupModal()" class="px-4 py-2 text-xs font-semibold text-slate-600 rounded-xl hover:bg-slate-100">Cancel</button>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-5 py-2 rounded-xl shadow-sm transition">Apply Top-up</button>
            </div>
        </form>
    </div>
</div>

<script>
function openTopupModal(userId, userName, currentLimit) {
    document.getElementById('topupUserName').innerText = userName;
    document.getElementById('topupForm').action = '{{ url('admin/users') }}/' + userId + '/topup-quota';
    document.getElementById('topupModal').classList.remove('hidden');
}
function closeTopupModal() {
    document.getElementById('topupModal').classList.add('hidden');
}
function setTopupAmount(amt) {
    document.getElementById('extraUnitsInput').value = amt;
}
</script>
@endsection