@extends('layouts.admin')
@section('title', 'API Usage & Spend Monitoring')
@section('content')

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Google Places API Usage & Costs</h1>
            <p class="text-sm text-slate-500 mt-1">Track upstream API volume, latency, and Google Cloud billing consumption</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.api-usage.export') }}" class="inline-flex items-center px-4 py-2 bg-white border border-slate-300 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50 shadow-sm">
                <i data-lucide="download" class="w-4 h-4 mr-2"></i> Export CSV
            </a>
        </div>
    </div>

    <!-- Enterprise Pricing Banner -->
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 flex items-start gap-3">
        <div class="p-2 bg-blue-100 text-blue-700 rounded-lg">
            <i data-lucide="info" class="w-5 h-5"></i>
        </div>
        <div>
            <h3 class="text-sm font-bold text-blue-900">Google Places (New) — Enterprise Tier Billing ($35.00 / 1,000 requests)</h3>
            <p class="text-xs text-blue-700 mt-0.5 leading-relaxed">
                Because our search queries include <code class="bg-blue-100/80 px-1 py-0.5 rounded font-mono font-semibold">places.rating</code> and <code class="bg-blue-100/80 px-1 py-0.5 rounded font-mono font-semibold">places.userRatingCount</code> in the field mask, Google Cloud bills this usage under the <strong>Places Text Search Enterprise SKU</strong> ($35.00/1k = $0.035 per search request).
            </p>
        </div>
    </div>

    <!-- Spend & Usage KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Calls Today</span>
                <div class="p-2 bg-blue-50 text-blue-600 rounded-lg">
                    <i data-lucide="activity" class="w-5 h-5"></i>
                </div>
            </div>
            <p class="text-2xl font-bold text-slate-800 mt-2">{{ number_format($totalCallsToday) }}</p>
            <p class="text-xs text-slate-400 mt-1">Est. Spend: ${{ number_format($dailySpendUsd, 3) }} USD</p>
        </div>
        <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-emerald-600 uppercase tracking-wider">Month-to-Date Volume</span>
                <div class="p-2 bg-emerald-50 text-emerald-600 rounded-lg">
                    <i data-lucide="calendar" class="w-5 h-5"></i>
                </div>
            </div>
            <p class="text-2xl font-bold text-emerald-600 mt-2">{{ number_format($totalCallsMonth) }}</p>
            <p class="text-xs text-slate-400 mt-1">Est. Spend: ${{ number_format($monthlySpendUsd, 2) }} USD</p>
        </div>
        <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-purple-600 uppercase tracking-wider">All-Time API Spend</span>
                <div class="p-2 bg-purple-50 text-purple-600 rounded-lg">
                    <i data-lucide="dollar-sign" class="w-5 h-5"></i>
                </div>
            </div>
            <p class="text-2xl font-bold text-purple-600 mt-2">${{ number_format($allTimeSpendUsd, 2) }} <span class="text-xs font-normal text-slate-500">USD</span></p>
            <p class="text-xs text-slate-400 mt-1">{{ number_format($totalCallsAllTime) }} total requests</p>
        </div>
        <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-amber-600 uppercase tracking-wider">Avg Latency (Today)</span>
                <div class="p-2 bg-amber-50 text-amber-600 rounded-lg">
                    <i data-lucide="timer" class="w-5 h-5"></i>
                </div>
            </div>
            <p class="text-2xl font-bold text-slate-800 mt-2">{{ number_format($avgResponseTime, 0) }} <span class="text-sm text-slate-400">ms</span></p>
            <p class="text-xs text-slate-400 mt-1">Error Rate: {{ $errorRate }}% ({{ $errorCount }} errors)</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
        <form method="GET" action="{{ route('admin.api-usage.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Endpoint or query..." class="border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <select name="tenant_id" class="border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">All Tenants</option>
                @foreach($tenants as $tenant)
                <option value="{{ $tenant->id }}" {{ request('tenant_id') == $tenant->id ? 'selected' : '' }}>{{ $tenant->company_name }}</option>
                @endforeach
            </select>
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <div class="flex gap-2">
                <button type="submit" class="flex-1 bg-slate-900 text-white rounded-lg text-sm font-medium py-2 hover:bg-slate-800">Filter</button>
                <a href="{{ route('admin.api-usage.index') }}" class="px-3 py-2 border border-slate-300 rounded-lg text-sm text-slate-600 hover:bg-slate-50">Reset</a>
            </div>
        </form>
    </div>

    <!-- Logs Table -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-5 border-b border-slate-200">
            <h2 class="text-base font-bold text-slate-800">API Call Logs</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-500 uppercase text-xs">
                    <tr>
                        <th class="px-5 py-3">Timestamp</th>
                        <th class="px-5 py-3">Tenant</th>
                        <th class="px-5 py-3">Provider</th>
                        <th class="px-5 py-3">Endpoint</th>
                        <th class="px-5 py-3 text-center">Status</th>
                        <th class="px-5 py-3 text-right">Latency</th>
                        <th class="px-5 py-3 text-right">Cost (USD)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($logs as $log)
                        <tr class="hover:bg-slate-50/50">
                            <td class="px-5 py-3 text-xs text-slate-500 font-mono">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                            <td class="px-5 py-3 font-medium text-slate-800">{{ $log->tenant?->company_name ?? 'N/A' }}</td>
                            <td class="px-5 py-3"><span class="px-2 py-0.5 text-xs bg-slate-100 text-slate-700 rounded-md font-semibold">{{ ucfirst($log->provider ?? 'Google') }}</span></td>
                            <td class="px-5 py-3 font-mono text-xs text-slate-600 truncate max-w-xs">{{ $log->endpoint }}</td>
                            <td class="px-5 py-3 text-center">
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ ($log->response_status >= 200 && $log->response_status < 300) ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $log->response_status }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right font-mono text-xs">{{ number_format($log->response_time_ms ?? 0) }}ms</td>
                            <td class="px-5 py-3 text-right font-mono text-xs text-slate-700 font-semibold">${{ number_format($log->estimated_cost ?? 0.035, 4) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-8 text-center text-slate-400 text-sm">No API usage logs found matching criteria.</td>
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