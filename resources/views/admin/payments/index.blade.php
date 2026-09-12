@extends('layouts.admin')
@section('title', 'Payments')
@section('content')

{{-- Summary Cards --}}
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
            <div class="w-12 h-12 bg-amber-50 rounded-xl flex items-center justify-center">
                <i data-lucide="clock" class="w-6 h-6 text-amber-600"></i>
            </div>
            <span class="text-xs font-medium px-2 py-1 rounded-full bg-amber-50 text-amber-600">Pending</span>
        </div>
        <p class="text-3xl font-bold text-slate-900">₹{{ number_format($totalPending, 0) }}</p>
        <p class="text-sm text-slate-500 mt-1">Pending Amount</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-red-50 rounded-xl flex items-center justify-center">
                <i data-lucide="x-circle" class="w-6 h-6 text-red-600"></i>
            </div>
            <span class="text-xs font-medium px-2 py-1 rounded-full bg-red-50 text-red-600">Failed</span>
        </div>
        <p class="text-3xl font-bold text-slate-900">{{ $totalFailed }}</p>
        <p class="text-sm text-slate-500 mt-1">Failed Payments</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center">
                <i data-lucide="receipt" class="w-6 h-6 text-blue-600"></i>
            </div>
            <span class="text-xs font-medium px-2 py-1 rounded-full bg-blue-50 text-blue-600">All</span>
        </div>
        <p class="text-3xl font-bold text-slate-900">{{ number_format($totalCount) }}</p>
        <p class="text-sm text-slate-500 mt-1">Total Payments</p>
    </div>
</div>

{{-- Filters --}}
<div class="bg-white rounded-xl shadow-sm border border-slate-100 mb-6">
    <div class="p-6">
        <form method="GET" action="{{ route('admin.payments.index') }}" class="flex flex-wrap items-end gap-4">
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">Search</label>
                <div class="relative">
                    <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Order ID, Payment ID, Tenant..."
                           class="pl-10 pr-4 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 w-64">
                </div>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">Status</label>
                <select name="status" class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All Statuses</option>
                    <option value="success" {{ request('status') === 'success' ? 'selected' : '' }}>Completed</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                    <option value="refunded" {{ request('status') === 'refunded' ? 'selected' : '' }}>Refunded</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">Tenant</label>
                <select name="tenant_id" class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All Tenants</option>
                    @foreach($tenants as $tenant)
                    <option value="{{ $tenant->id }}" {{ request('tenant_id') == $tenant->id ? 'selected' : '' }}>{{ $tenant->company_name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">From</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                       class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">To</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                       class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                    Filter
                </button>
                <a href="{{ route('admin.payments.index') }}" class="px-4 py-2 rounded-lg text-sm font-medium text-slate-600 hover:bg-slate-100 transition-colors">
                    Reset
                </a>
                <a href="{{ route('admin.payments.export', request()->query()) }}" class="inline-flex items-center px-4 py-2 bg-slate-100 border border-slate-300 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-200 transition">
                    <i data-lucide="download" class="w-4 h-4 mr-1.5"></i> Export CSV
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Payments Table --}}
<div class="bg-white rounded-xl shadow-sm border border-slate-100">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-slate-100">
                    <th class="text-left py-3 px-6 text-xs font-medium text-slate-500 uppercase tracking-wider">Tenant</th>
                    <th class="text-right py-3 px-6 text-xs font-medium text-slate-500 uppercase tracking-wider">Amount</th>
                    <th class="text-left py-3 px-6 text-xs font-medium text-slate-500 uppercase tracking-wider">Gateway</th>
                    <th class="text-center py-3 px-6 text-xs font-medium text-slate-500 uppercase tracking-wider">Status</th>
                    <th class="text-left py-3 px-6 text-xs font-medium text-slate-500 uppercase tracking-wider">Type</th>
                    <th class="text-left py-3 px-6 text-xs font-medium text-slate-500 uppercase tracking-wider">Order ID</th>
                    <th class="text-left py-3 px-6 text-xs font-medium text-slate-500 uppercase tracking-wider">Date</th>
                    <th class="text-right py-3 px-6 text-xs font-medium text-slate-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($payments as $payment)
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="py-3 px-6">
                        <p class="font-medium text-slate-900">{{ $payment->tenant->company_name ?? 'N/A' }}</p>
                        <p class="text-xs text-slate-400">{{ $payment->user->name ?? '' }}</p>
                    </td>
                    <td class="py-3 px-6 text-right">
                        <span class="font-semibold text-slate-900">₹{{ number_format($payment->amount, 2) }}</span>
                        <p class="text-xs text-slate-400">{{ $payment->currency ?? 'INR' }}</p>
                    </td>
                    <td class="py-3 px-6 text-sm text-slate-600">{{ ucfirst($payment->gateway ?? '-') }}</td>
                    <td class="py-3 px-6 text-center">
                        @php
                            $statusColors = [
                                'success' => 'bg-emerald-50 text-emerald-700',
                                'completed' => 'bg-emerald-50 text-emerald-700',
                                'pending' => 'bg-amber-50 text-amber-700',
                                'failed' => 'bg-red-50 text-red-700',
                                'refunded' => 'bg-violet-50 text-violet-700',
                            ];
                            $sColor = $statusColors[$payment->status] ?? 'bg-slate-50 text-slate-700';
                        @endphp
                        <span class="text-xs font-medium px-2 py-1 rounded-full {{ $sColor }}">{{ ucfirst($payment->status) }}</span>
                    </td>
                    <td class="py-3 px-6 text-sm text-slate-600">{{ ucfirst($payment->type ?? '-') }}</td>
                    <td class="py-3 px-6 text-sm text-slate-500 font-mono">{{ $payment->gateway_order_id ?? '-' }}</td>
                    <td class="py-3 px-6 text-sm text-slate-500">{{ $payment->created_at->format('d M Y H:i') }}</td>
                    <td class="py-3 px-6 text-right">
                        @if($payment->status === 'success')
                            <form action="{{ route('admin.payments.refund', $payment->id) }}" method="POST" onsubmit="return confirm('Refund this payment of ₹{{ $payment->amount }}?')" class="inline-block">
                                @csrf
                                <button type="submit" class="px-2.5 py-1 text-xs font-semibold text-red-600 bg-red-50 hover:bg-red-100 rounded-md transition">
                                    Refund
                                </button>
                            </form>
                        @else
                            <span class="text-xs text-slate-400">—</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="py-12 text-center">
                        <div class="flex flex-col items-center">
                            <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mb-4">
                                <i data-lucide="credit-card" class="w-8 h-8 text-slate-300"></i>
                            </div>
                            <p class="text-slate-500 font-medium">No payments found</p>
                            <p class="text-sm text-slate-400 mt-1">Payments will appear here once tenants make purchases</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($payments->hasPages())
    <div class="px-6 py-4 border-t border-slate-100">
        {{ $payments->links() }}
    </div>
    @endif
</div>

@push('scripts')
<script>lucide.createIcons();</script>
@endpush
@endsection