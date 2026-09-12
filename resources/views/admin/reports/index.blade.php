@extends('layouts.admin')
@section('title', 'Reports')
@section('content')

<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Reports & Business Analytics</h1>
        <p class="text-sm text-slate-500 mt-1">Cross-tenant revenue trends, search usage metrics, and sales conversion rates</p>
    </div>
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.reports.export') }}" class="inline-flex items-center px-4 py-2 bg-white border border-slate-300 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50 shadow-sm transition">
            <i data-lucide="download" class="w-4 h-4 mr-2"></i> Export Monthly Analytics (CSV)
        </a>
    </div>
</div>
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-emerald-50 rounded-xl flex items-center justify-center">
                <i data-lucide="indian-rupee" class="w-6 h-6 text-emerald-600"></i>
            </div>
            <span class="text-xs font-medium px-2 py-1 rounded-full bg-emerald-50 text-emerald-600">Revenue</span>
        </div>
        <p class="text-3xl font-bold text-slate-900">₹{{ number_format($totalRevenue, 0) }}</p>
        <p class="text-sm text-slate-500 mt-1">Total Revenue</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-violet-50 rounded-xl flex items-center justify-center">
                <i data-lucide="search" class="w-6 h-6 text-violet-600"></i>
            </div>
        </div>
        <p class="text-3xl font-bold text-slate-900">{{ number_format($totalSearches) }}</p>
        <p class="text-sm text-slate-500 mt-1">Total Searches</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center">
                <i data-lucide="target" class="w-6 h-6 text-blue-600"></i>
            </div>
        </div>
        <p class="text-3xl font-bold text-slate-900">{{ number_format($totalLeads) }}</p>
        <p class="text-sm text-slate-500 mt-1">Total Leads</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-amber-50 rounded-xl flex items-center justify-center">
                <i data-lucide="building-2" class="w-6 h-6 text-amber-600"></i>
            </div>
            <span class="text-xs font-medium px-2 py-1 rounded-full bg-amber-50 text-amber-600">Active</span>
        </div>
        <p class="text-3xl font-bold text-slate-900">{{ $activeTenants }}</p>
        <p class="text-sm text-slate-500 mt-1">Active Tenants</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    {{-- Revenue by Month --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-100">
        <div class="p-6 border-b border-slate-100">
            <h3 class="text-lg font-semibold text-slate-900">Revenue by Month</h3>
            <p class="text-sm text-slate-500">Last 6 months breakdown</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-slate-100">
                        <th class="text-left py-3 px-6 text-xs font-medium text-slate-500 uppercase tracking-wider">Month</th>
                        <th class="text-right py-3 px-6 text-xs font-medium text-slate-500 uppercase tracking-wider">Revenue</th>
                        <th class="text-right py-3 px-6 text-xs font-medium text-slate-500 uppercase tracking-wider">Payments</th>
                        <th class="text-left py-3 px-6 text-xs font-medium text-slate-500 uppercase tracking-wider">Bar</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @php 
                        $revCol = array_column($revenueByMonth, 'revenue'); 
                        $maxRevenue = (!empty($revCol) ? max($revCol) : 0) ?: 1; 
                    @endphp
                    @foreach($revenueByMonth as $month)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="py-3 px-6 text-sm font-medium text-slate-900">{{ $month['month'] }}</td>
                        <td class="py-3 px-6 text-right text-sm font-semibold text-emerald-600">₹{{ number_format($month['revenue'], 0) }}</td>
                        <td class="py-3 px-6 text-right text-sm text-slate-600">{{ $month['count'] }}</td>
                        <td class="py-3 px-6 w-48">
                            <div class="w-full bg-slate-100 rounded-full h-2">
                                <div class="bg-emerald-500 h-2 rounded-full" style="width: {{ ($month['revenue'] / $maxRevenue) * 100 }}%"></div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Lead Conversion Stats --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-100">
        <div class="p-6 border-b border-slate-100">
            <h3 class="text-lg font-semibold text-slate-900">Lead Pipeline</h3>
            <p class="text-sm text-slate-500">Conversion funnel across all tenants</p>
        </div>
        <div class="p-6 space-y-4">
            @php
                $totalAllLeads = array_sum($leadStats) ?: 1;
                $stageConfig = [
                    'new' => ['label' => 'New', 'color' => 'bg-blue-500', 'bg' => 'bg-blue-50', 'text' => 'text-blue-700'],
                    'contacted' => ['label' => 'Contacted', 'color' => 'bg-amber-500', 'bg' => 'bg-amber-50', 'text' => 'text-amber-700'],
                    'qualified' => ['label' => 'Qualified', 'color' => 'bg-violet-500', 'bg' => 'bg-violet-50', 'text' => 'text-violet-700'],
                    'proposal' => ['label' => 'Proposal', 'color' => 'bg-cyan-500', 'bg' => 'bg-cyan-50', 'text' => 'text-cyan-700'],
                    'won' => ['label' => 'Won', 'color' => 'bg-emerald-500', 'bg' => 'bg-emerald-50', 'text' => 'text-emerald-700'],
                    'lost' => ['label' => 'Lost', 'color' => 'bg-red-500', 'bg' => 'bg-red-50', 'text' => 'text-red-700'],
                ];
            @endphp
            @foreach($stageConfig as $key => $config)
            <div>
                <div class="flex items-center justify-between mb-1">
                    <span class="text-sm font-medium text-slate-700">{{ $config['label'] }}</span>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $config['bg'] }} {{ $config['text'] }}">
                            {{ $leadStats[$key] ?? 0 }}
                        </span>
                        <span class="text-xs text-slate-400">{{ round((($leadStats[$key] ?? 0) / $totalAllLeads) * 100, 1) }}%</span>
                    </div>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-2.5">
                    <div class="{{ $config['color'] }} h-2.5 rounded-full transition-all" style="width: {{ (($leadStats[$key] ?? 0) / $totalAllLeads) * 100 }}%"></div>
                </div>
            </div>
            @endforeach

            <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
                <span class="text-sm text-slate-600">Total Won Value</span>
                <span class="text-lg font-bold text-emerald-600">₹{{ number_format($totalLeadValue, 0) }}</span>
            </div>
        </div>
    </div>
</div>

{{-- Top 10 Tenants by Search --}}
<div class="bg-white rounded-xl shadow-sm border border-slate-100">
    <div class="p-6 border-b border-slate-100">
        <h3 class="text-lg font-semibold text-slate-900">Top 10 Tenants by Search Usage</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-slate-100">
                    <th class="text-left py-3 px-6 text-xs font-medium text-slate-500 uppercase tracking-wider">#</th>
                    <th class="text-left py-3 px-6 text-xs font-medium text-slate-500 uppercase tracking-wider">Tenant</th>
                    <th class="text-left py-3 px-6 text-xs font-medium text-slate-500 uppercase tracking-wider">Status</th>
                    <th class="text-right py-3 px-6 text-xs font-medium text-slate-500 uppercase tracking-wider">Searches</th>
                    <th class="text-left py-3 px-6 text-xs font-medium text-slate-500 uppercase tracking-wider">Usage Bar</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @php $maxSearches = $topTenantsBySearch->first()?->searches_count ?: 1; @endphp
                @forelse($topTenantsBySearch as $i => $tenant)
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="py-3 px-6">
                        <span class="w-7 h-7 rounded-full {{ $i < 3 ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-600' }} inline-flex items-center justify-center text-xs font-bold">
                            {{ $i + 1 }}
                        </span>
                    </td>
                    <td class="py-3 px-6">
                        <p class="font-medium text-slate-900">{{ $tenant->company_name }}</p>
                        <p class="text-xs text-slate-400">{{ $tenant->email }}</p>
                    </td>
                    <td class="py-3 px-6">
                        <span class="text-xs font-medium px-2 py-1 rounded-full
                            {{ $tenant->status === 'active' ? 'bg-emerald-50 text-emerald-700' : ($tenant->status === 'trial' ? 'bg-amber-50 text-amber-700' : 'bg-red-50 text-red-700') }}">
                            {{ ucfirst($tenant->status) }}
                        </span>
                    </td>
                    <td class="py-3 px-6 text-right font-semibold text-slate-900">{{ number_format($tenant->searches_count) }}</td>
                    <td class="py-3 px-6 w-48">
                        <div class="w-full bg-slate-100 rounded-full h-2">
                            <div class="bg-violet-500 h-2 rounded-full" style="width: {{ ($tenant->searches_count / $maxSearches) * 100 }}%"></div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="py-12 text-center">
                        <div class="flex flex-col items-center">
                            <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mb-4">
                                <i data-lucide="search" class="w-8 h-8 text-slate-300"></i>
                            </div>
                            <p class="text-slate-500 font-medium">No search data yet</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>lucide.createIcons();</script>
@endpush
@endsection
