@extends('layouts.admin')

@section('title', 'Security & Login Monitoring')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Security & Authentication Audits</h1>
            <p class="text-sm text-slate-500 mt-1">Monitor login attempts, brute-force defenses, and revoke active API tokens</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.security.export') }}" class="inline-flex items-center px-4 py-2 bg-white border border-slate-300 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50 shadow-sm">
                <i data-lucide="download" class="w-4 h-4 mr-2"></i> Export CSV
            </a>
        </div>
    </div>

    <!-- Security KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Logins (Today)</span>
                <div class="p-2 bg-blue-50 text-blue-600 rounded-lg">
                    <i data-lucide="log-in" class="w-5 h-5"></i>
                </div>
            </div>
            <p class="text-2xl font-bold text-slate-800 mt-2">{{ number_format($totalLoginsToday) }}</p>
        </div>
        <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-emerald-600 uppercase tracking-wider">Successful Logins</span>
                <div class="p-2 bg-emerald-50 text-emerald-600 rounded-lg">
                    <i data-lucide="shield-check" class="w-5 h-5"></i>
                </div>
            </div>
            <p class="text-2xl font-bold text-emerald-600 mt-2">{{ number_format($successfulLoginsToday) }}</p>
        </div>
        <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-red-500 uppercase tracking-wider">Failed Attempts</span>
                <div class="p-2 bg-red-50 text-red-600 rounded-lg">
                    <i data-lucide="shield-alert" class="w-5 h-5"></i>
                </div>
            </div>
            <p class="text-2xl font-bold text-red-600 mt-2">{{ number_format($failedLoginsToday) }}</p>
        </div>
        <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-purple-600 uppercase tracking-wider">Active Tokens (Sanctum)</span>
                <div class="p-2 bg-purple-50 text-purple-600 rounded-lg">
                    <i data-lucide="key" class="w-5 h-5"></i>
                </div>
            </div>
            <p class="text-2xl font-bold text-purple-600 mt-2">{{ number_format($activeTokensCount) }}</p>
        </div>
    </div>

    <!-- Active Sanctum Tokens (Revoke Action) -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-200 flex items-center justify-between">
            <h2 class="text-base font-bold text-slate-800 flex items-center gap-2">
                <i data-lucide="key" class="w-4 h-4 text-purple-600"></i> Active Access Tokens
            </h2>
            <span class="text-xs text-slate-500">Auto-expires in 30 days</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-500 uppercase text-xs">
                    <tr>
                        <th class="px-5 py-3">Token Name</th>
                        <th class="px-5 py-3">User</th>
                        <th class="px-5 py-3">Created</th>
                        <th class="px-5 py-3">Last Used</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($activeTokens as $tok)
                        <tr class="hover:bg-slate-50/50">
                            <td class="px-5 py-3.5 font-medium text-slate-800 flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                {{ $tok->name }}
                            </td>
                            <td class="px-5 py-3.5 text-slate-600">{{ $tok->tokenable?->name ?? 'User #' . $tok->tokenable_id }} ({{ $tok->tokenable?->email }})</td>
                            <td class="px-5 py-3.5 text-slate-500 text-xs">{{ $tok->created_at->format('M d, Y H:i') }}</td>
                            <td class="px-5 py-3.5 text-slate-500 text-xs">{{ $tok->last_used_at ? $tok->last_used_at->diffForHumans() : 'Never' }}</td>
                            <td class="px-5 py-3.5 text-right">
                                <form action="{{ route('admin.security.tokens.revoke', $tok->id) }}" method="POST" onsubmit="return confirm('Revoke this session token immediately? User will be logged out.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs font-semibold text-red-600 hover:text-red-800 bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-md transition">
                                        Revoke
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-8 text-center text-slate-400 text-sm">No active tokens found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Login Logs Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-200">
            <h2 class="text-base font-bold text-slate-800 mb-4 flex items-center gap-2">
                <i data-lucide="shield" class="w-4 h-4 text-blue-600"></i> Login Attempt Audit Trail
            </h2>
            <form method="GET" action="{{ route('admin.security.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search user, email, IP..." class="border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <select name="status" class="border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All Statuses</option>
                    <option value="success" {{ request('status') === 'success' ? 'selected' : '' }}>Success Only</option>
                    <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed Only</option>
                </select>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <div class="flex gap-2">
                    <button type="submit" class="flex-1 bg-slate-900 text-white rounded-lg text-sm font-medium py-2 hover:bg-slate-800">Filter</button>
                    <a href="{{ route('admin.security.index') }}" class="px-3 py-2 border border-slate-300 rounded-lg text-sm text-slate-600 hover:bg-slate-50">Reset</a>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-500 uppercase text-xs">
                    <tr>
                        <th class="px-5 py-3">Timestamp</th>
                        <th class="px-5 py-3">User</th>
                        <th class="px-5 py-3">IP Address</th>
                        <th class="px-5 py-3">Device / Client</th>
                        <th class="px-5 py-3 text-right">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($logs as $log)
                        <tr class="hover:bg-slate-50/50">
                            <td class="px-5 py-3 text-xs text-slate-500 font-mono">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                            <td class="px-5 py-3">
                                <span class="font-medium text-slate-800">{{ $log->user?->name ?? 'Guest' }}</span>
                                <span class="block text-xs text-slate-400">{{ $log->user?->email }}</span>
                            </td>
                            <td class="px-5 py-3 font-mono text-xs text-slate-600">{{ $log->ip_address }}</td>
                            <td class="px-5 py-3 text-xs text-slate-600">{{ $log->device ?? $log->user_agent }}</td>
                            <td class="px-5 py-3 text-right">
                                @if($log->status === 'success')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                        Success
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800">
                                        Failed
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-8 text-center text-slate-400 text-sm">No login logs matching criteria.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-200">
            {{ $logs->links() }}
        </div>
    </div>
</div>
@endsection
