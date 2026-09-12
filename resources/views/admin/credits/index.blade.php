@extends('layouts.admin')
@section('title', 'Credit Wallets')
@section('content')

{{-- Flash Messages --}}
@if(session('success'))
<div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl flex items-center justify-between" x-data="{ show: true }" x-show="show">
    <div class="flex items-center">
        <i data-lucide="check-circle" class="w-5 h-5 mr-2"></i>
        {{ session('success') }}
    </div>
    <button @click="show = false"><i data-lucide="x" class="w-4 h-4"></i></button>
</div>
@endif

{{-- Summary Cards --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center">
                <i data-lucide="wallet" class="w-6 h-6 text-blue-600"></i>
            </div>
            <span class="text-xs font-medium px-2 py-1 rounded-full bg-blue-50 text-blue-600">Total</span>
        </div>
        <p class="text-3xl font-bold text-slate-900">{{ number_format($totalBalance, 0) }}</p>
        <p class="text-sm text-slate-500 mt-1">Total Balance</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-emerald-50 rounded-xl flex items-center justify-center">
                <i data-lucide="trending-up" class="w-6 h-6 text-emerald-600"></i>
            </div>
            <span class="text-xs font-medium px-2 py-1 rounded-full bg-emerald-50 text-emerald-600">Earned</span>
        </div>
        <p class="text-3xl font-bold text-slate-900">{{ number_format($totalEarned, 0) }}</p>
        <p class="text-sm text-slate-500 mt-1">Total Earned</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-amber-50 rounded-xl flex items-center justify-center">
                <i data-lucide="trending-down" class="w-6 h-6 text-amber-600"></i>
            </div>
            <span class="text-xs font-medium px-2 py-1 rounded-full bg-amber-50 text-amber-600">Spent</span>
        </div>
        <p class="text-3xl font-bold text-slate-900">{{ number_format($totalSpent, 0) }}</p>
        <p class="text-sm text-slate-500 mt-1">Total Spent</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Wallets Table --}}
    <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-slate-100">
        <div class="p-6 border-b border-slate-100">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <h3 class="text-lg font-semibold text-slate-900">Credit Wallets</h3>
                <div class="flex items-center gap-3">
                    <form method="GET" action="{{ route('admin.credits.index') }}" class="flex items-center">
                        <div class="relative">
                            <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search tenants..."
                                   class="pl-10 pr-4 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent w-64">
                        </div>
                    </form>
                    <a href="{{ route('admin.credits.export') }}" class="inline-flex items-center px-3 py-2 bg-slate-100 border border-slate-200 rounded-lg text-xs font-semibold text-slate-700 hover:bg-slate-200 transition">
                        <i data-lucide="download" class="w-3.5 h-3.5 mr-1.5"></i> Export CSV
                    </a>
                </div>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-slate-100">
                        <th class="text-left py-3 px-6 text-xs font-medium text-slate-500 uppercase tracking-wider">Tenant</th>
                        <th class="text-right py-3 px-6 text-xs font-medium text-slate-500 uppercase tracking-wider">Balance</th>
                        <th class="text-right py-3 px-6 text-xs font-medium text-slate-500 uppercase tracking-wider">Earned</th>
                        <th class="text-right py-3 px-6 text-xs font-medium text-slate-500 uppercase tracking-wider">Spent</th>
                        <th class="text-center py-3 px-6 text-xs font-medium text-slate-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($wallets as $wallet)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="py-3 px-6">
                            <p class="font-medium text-slate-900">{{ $wallet->tenant->company_name ?? 'N/A' }}</p>
                            <p class="text-xs text-slate-400">{{ $wallet->tenant->email ?? '' }}</p>
                        </td>
                        <td class="py-3 px-6 text-right">
                            <span class="font-semibold {{ $wallet->balance > 0 ? 'text-emerald-600' : 'text-red-600' }}">
                                {{ number_format($wallet->balance, 0) }}
                            </span>
                        </td>
                        <td class="py-3 px-6 text-right text-sm text-slate-600">{{ number_format($wallet->total_earned, 0) }}</td>
                        <td class="py-3 px-6 text-right text-sm text-slate-600">{{ number_format($wallet->total_spent, 0) }}</td>
                        <td class="py-3 px-6 text-center">
                            <a href="{{ route('admin.credits.transactions', $wallet->tenant_id) }}"
                               class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-medium bg-blue-50 text-blue-700 hover:bg-blue-100 transition-colors">
                                <i data-lucide="list" class="w-3 h-3 mr-1"></i> Transactions
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-12 text-center">
                            <div class="flex flex-col items-center">
                                <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mb-4">
                                    <i data-lucide="wallet" class="w-8 h-8 text-slate-300"></i>
                                </div>
                                <p class="text-slate-500 font-medium">No credit wallets found</p>
                                <p class="text-sm text-slate-400 mt-1">Credit wallets will appear when tenants are created</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($wallets->hasPages())
        <div class="px-6 py-4 border-t border-slate-100">
            {{ $wallets->links() }}
        </div>
        @endif
    </div>

    {{-- Adjustment Form --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 h-fit">
        <div class="p-6 border-b border-slate-100">
            <h3 class="text-lg font-semibold text-slate-900">Manual Adjustment</h3>
            <p class="text-sm text-slate-500 mt-1">Add or deduct credits from a tenant's wallet</p>
        </div>
        <form method="POST" action="{{ route('admin.credits.adjust') }}" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Tenant</label>
                <select name="tenant_id" required
                        class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Select tenant...</option>
                    @foreach($tenants as $tenant)
                    <option value="{{ $tenant->id }}">{{ $tenant->company_name }}</option>
                    @endforeach
                </select>
                @error('tenant_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Type</label>
                <select name="type" required
                        class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="credit">Add Credits</option>
                    <option value="debit">Deduct Credits</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Credits</label>
                <input type="number" name="credits" min="0.01" step="0.01" required placeholder="Enter amount"
                       class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                @error('credits') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Description</label>
                <textarea name="description" required rows="3" placeholder="Reason for adjustment..."
                          class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                @error('description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <button type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg text-sm transition-colors flex items-center justify-center">
                <i data-lucide="check" class="w-4 h-4 mr-2"></i>
                Apply Adjustment
            </button>
        </form>
    </div>
</div>

@push('scripts')
<script>lucide.createIcons();</script>
@endpush
@endsection