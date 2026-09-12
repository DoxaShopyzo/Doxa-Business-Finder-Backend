@extends('layouts.admin')
@section('title', 'Dashboard')
@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center">
                <i data-lucide="building-2" class="w-6 h-6 text-blue-600"></i>
            </div>
            <span class="text-xs font-medium px-2 py-1 rounded-full bg-blue-50 text-blue-600">{{ $stats['active_tenants'] }} active</span>
        </div>
        <p class="text-3xl font-bold text-slate-900">{{ $stats['total_tenants'] }}</p>
        <p class="text-sm text-slate-500 mt-1">Total Tenants</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-emerald-50 rounded-xl flex items-center justify-center">
                <i data-lucide="users" class="w-6 h-6 text-emerald-600"></i>
            </div>
            <span class="text-xs font-medium px-2 py-1 rounded-full bg-emerald-50 text-emerald-600">Active</span>
        </div>
        <p class="text-3xl font-bold text-slate-900">{{ $stats['total_users'] }}</p>
        <p class="text-sm text-slate-500 mt-1">Total Users</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-amber-50 rounded-xl flex items-center justify-center">
                <i data-lucide="indian-rupee" class="w-6 h-6 text-amber-600"></i>
            </div>
            <span class="text-xs font-medium px-2 py-1 rounded-full bg-amber-50 text-amber-600">This Month</span>
        </div>
        <p class="text-3xl font-bold text-slate-900">₹{{ number_format($stats['revenue_this_month'], 0) }}</p>
        <p class="text-sm text-slate-500 mt-1">Revenue</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-violet-50 rounded-xl flex items-center justify-center">
                <i data-lucide="search" class="w-6 h-6 text-violet-600"></i>
            </div>
        </div>
        <p class="text-3xl font-bold text-slate-900">{{ number_format($stats['total_searches']) }}</p>
        <p class="text-sm text-slate-500 mt-1">Total Searches</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
        <h3 class="text-lg font-semibold text-slate-900 mb-4">Recent Tenants</h3>
        <div class="space-y-3">
            @forelse($recentTenants as $tenant)
            <div class="flex items-center justify-between py-2 border-b border-slate-50 last:border-0">
                <div>
                    <p class="font-medium text-slate-900">{{ $tenant->company_name }}</p>
                    <p class="text-sm text-slate-500">{{ $tenant->email }}</p>
                </div>
                <span class="text-xs font-medium px-2 py-1 rounded-full
                    {{ $tenant->status === 'active' ? 'bg-emerald-50 text-emerald-700' : ($tenant->status === 'trial' ? 'bg-amber-50 text-amber-700' : 'bg-red-50 text-red-700') }}">
                    {{ ucfirst($tenant->status) }}
                </span>
            </div>
            @empty
            <p class="text-sm text-slate-400">No tenants yet</p>
            @endforelse
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
        <h3 class="text-lg font-semibold text-slate-900 mb-4">Quick Stats</h3>
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <span class="text-slate-600">Active Subscriptions</span>
                <span class="font-semibold text-slate-900">{{ $stats['active_subscriptions'] }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-slate-600">Trial Tenants</span>
                <span class="font-semibold text-slate-900">{{ $stats['trial_tenants'] }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-slate-600">Total Leads (All Tenants)</span>
                <span class="font-semibold text-slate-900">{{ number_format($stats['total_leads']) }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-slate-600">Total Revenue</span>
                <span class="font-semibold text-emerald-600">₹{{ number_format($stats['revenue_total'], 0) }}</span>
            </div>
        </div>
    </div>
</div>
@endsection