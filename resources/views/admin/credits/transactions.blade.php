@extends('layouts.admin')
@section('title', 'Credit Transactions - ' . $tenant->company_name)
@section('content')

{{-- Breadcrumb --}}
<div class="mb-6 flex items-center text-sm text-slate-500">
    <a href="{{ route('admin.credits.index') }}" class="hover:text-blue-600 transition-colors">Credit Wallets</a>
    <i data-lucide="chevron-right" class="w-4 h-4 mx-2"></i>
    <span class="text-slate-900 font-medium">{{ $tenant->company_name }}</span>
</div>

{{-- Wallet Summary --}}
@if($wallet)
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center">
                <i data-lucide="wallet" class="w-6 h-6 text-blue-600"></i>
            </div>
        </div>
        <p class="text-3xl font-bold {{ $wallet->balance > 0 ? 'text-emerald-600' : 'text-red-600' }}">{{ number_format($wallet->balance, 0) }}</p>
        <p class="text-sm text-slate-500 mt-1">Current Balance</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-emerald-50 rounded-xl flex items-center justify-center">
                <i data-lucide="trending-up" class="w-6 h-6 text-emerald-600"></i>
            </div>
        </div>
        <p class="text-3xl font-bold text-slate-900">{{ number_format($wallet->total_earned, 0) }}</p>
        <p class="text-sm text-slate-500 mt-1">Total Earned</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-amber-50 rounded-xl flex items-center justify-center">
                <i data-lucide="trending-down" class="w-6 h-6 text-amber-600"></i>
            </div>
        </div>
        <p class="text-3xl font-bold text-slate-900">{{ number_format($wallet->total_spent, 0) }}</p>
        <p class="text-sm text-slate-500 mt-1">Total Spent</p>
    </div>
</div>
@endif

{{-- Filters --}}
<div class="bg-white rounded-xl shadow-sm border border-slate-100 mb-6">
    <div class="p-6">
        <form method="GET" action="{{ route('admin.credits.transactions', $tenant->id) }}" class="flex flex-wrap items-end gap-4">
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">Transaction Type</label>
                <select name="type" class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All Types</option>
                    <option value="admin_credit" {{ request('type') === 'admin_credit' ? 'selected' : '' }}>Admin Credit</option>
                    <option value="admin_debit" {{ request('type') === 'admin_debit' ? 'selected' : '' }}>Admin Debit</option>
                    <option value="purchase" {{ request('type') === 'purchase' ? 'selected' : '' }}>Purchase</option>
                    <option value="usage" {{ request('type') === 'usage' ? 'selected' : '' }}>Usage</option>
                    <option value="refund" {{ request('type') === 'refund' ? 'selected' : '' }}>Refund</option>
                </select>
            </div>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                Filter
            </button>
            <a href="{{ route('admin.credits.transactions', $tenant->id) }}" class="px-4 py-2 rounded-lg text-sm font-medium text-slate-600 hover:bg-slate-100 transition-colors">
                Reset
            </a>
        </form>
    </div>
</div>

{{-- Transactions Table --}}
<div class="bg-white rounded-xl shadow-sm border border-slate-100">
    <div class="p-6 border-b border-slate-100">
        <h3 class="text-lg font-semibold text-slate-900">Transaction History</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-slate-100">
                    <th class="text-left py-3 px-6 text-xs font-medium text-slate-500 uppercase tracking-wider">Date</th>
                    <th class="text-left py-3 px-6 text-xs font-medium text-slate-500 uppercase tracking-wider">Type</th>
                    <th class="text-right py-3 px-6 text-xs font-medium text-slate-500 uppercase tracking-wider">Credits</th>
                    <th class="text-right py-3 px-6 text-xs font-medium text-slate-500 uppercase tracking-wider">Before</th>
                    <th class="text-right py-3 px-6 text-xs font-medium text-slate-500 uppercase tracking-wider">After</th>
                    <th class="text-left py-3 px-6 text-xs font-medium text-slate-500 uppercase tracking-wider">Description</th>
                    <th class="text-left py-3 px-6 text-xs font-medium text-slate-500 uppercase tracking-wider">User</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($transactions as $txn)
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="py-3 px-6 text-sm text-slate-600">{{ $txn->created_at->format('d M Y H:i') }}</td>
                    <td class="py-3 px-6">
                        @php
                            $typeColors = [
                                'admin_credit' => 'bg-emerald-50 text-emerald-700',
                                'admin_debit' => 'bg-red-50 text-red-700',
                                'purchase' => 'bg-blue-50 text-blue-700',
                                'usage' => 'bg-amber-50 text-amber-700',
                                'refund' => 'bg-violet-50 text-violet-700',
                            ];
                            $color = $typeColors[$txn->type] ?? 'bg-slate-50 text-slate-700';
                        @endphp
                        <span class="text-xs font-medium px-2 py-1 rounded-full {{ $color }}">{{ str_replace('_', ' ', ucfirst($txn->type)) }}</span>
                    </td>
                    <td class="py-3 px-6 text-right font-semibold {{ str_contains($txn->type, 'credit') || $txn->type === 'purchase' || $txn->type === 'refund' ? 'text-emerald-600' : 'text-red-600' }}">
                        {{ str_contains($txn->type, 'credit') || $txn->type === 'purchase' || $txn->type === 'refund' ? '+' : '-' }}{{ number_format($txn->credits, 0) }}
                    </td>
                    <td class="py-3 px-6 text-right text-sm text-slate-500">{{ number_format($txn->balance_before, 0) }}</td>
                    <td class="py-3 px-6 text-right text-sm text-slate-500">{{ number_format($txn->balance_after, 0) }}</td>
                    <td class="py-3 px-6 text-sm text-slate-600 max-w-xs truncate">{{ $txn->description ?? '-' }}</td>
                    <td class="py-3 px-6 text-sm text-slate-600">{{ $txn->user->name ?? 'System' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="py-12 text-center">
                        <div class="flex flex-col items-center">
                            <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mb-4">
                                <i data-lucide="receipt" class="w-8 h-8 text-slate-300"></i>
                            </div>
                            <p class="text-slate-500 font-medium">No transactions found</p>
                            <p class="text-sm text-slate-400 mt-1">This tenant has no credit transactions yet</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($transactions->hasPages())
    <div class="px-6 py-4 border-t border-slate-100">
        {{ $transactions->links() }}
    </div>
    @endif
</div>

@push('scripts')
<script>lucide.createIcons();</script>
@endpush
@endsection
