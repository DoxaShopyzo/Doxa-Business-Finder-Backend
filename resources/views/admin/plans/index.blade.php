@extends('layouts.admin')
@section('title', 'Subscription Plans')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Subscription Plans</h1>
            <p class="text-slate-500 text-sm mt-1">Manage pricing tiers, included search credits, and user limits</p>
        </div>
        <a href="{{ route('admin.plans.create') }}" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl shadow-sm transition">
            <i data-lucide="plus" class="w-4 h-4"></i> Create Plan
        </a>
    </div>

    <!-- Plans Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($plans as $plan)
            <div class="bg-white rounded-2xl border {{ $plan->is_active ? 'border-slate-200 shadow-sm' : 'border-dashed border-slate-300 opacity-60' }} p-6 flex flex-col justify-between hover:shadow-md transition">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-xs uppercase font-bold tracking-wider px-2.5 py-1 rounded-full {{ $plan->is_free ? 'bg-slate-100 text-slate-700' : 'bg-blue-100 text-blue-800' }}">
                            {{ $plan->billing_cycle ?? 'Monthly' }}
                        </span>
                        @if($plan->is_active)
                            <span class="text-xs text-emerald-600 font-semibold flex items-center gap-1">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Active
                            </span>
                        @else
                            <span class="text-xs text-slate-400 font-semibold">Disabled</span>
                        @endif
                    </div>

                    <h3 class="text-xl font-bold text-slate-900">{{ $plan->name }}</h3>
                    <p class="text-xs text-slate-500 mt-1 min-h-[32px]">{{ $plan->description ?? 'Standard subscription tier for businesses' }}</p>

                    <div class="my-6">
                        <span class="text-3xl font-extrabold text-slate-900">₹{{ number_format($plan->price, 0) }}</span>
                        <span class="text-xs text-slate-500 font-medium">/ {{ $plan->billing_cycle ?? 'month' }}</span>
                    </div>

                    <div class="space-y-3 py-4 border-t border-b border-slate-100 text-xs text-slate-600">
                        <div class="flex items-center justify-between">
                            <span class="flex items-center gap-2"><i data-lucide="coins" class="w-4 h-4 text-amber-500"></i> Monthly Credits</span>
                            <span class="font-bold text-slate-900">{{ number_format($plan->credits_included) }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="flex items-center gap-2"><i data-lucide="users" class="w-4 h-4 text-blue-500"></i> Max Team Users</span>
                            <span class="font-bold text-slate-900">{{ $plan->max_users }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="flex items-center gap-2"><i data-lucide="search" class="w-4 h-4 text-indigo-500"></i> Max Daily Searches</span>
                            <span class="font-bold text-slate-900">{{ $plan->max_searches_per_day ?? 100 }}</span>
                        </div>
                    </div>
                </div>

                <div class="pt-6">
                    <a href="{{ route('admin.plans.edit', $plan->id) }}" class="w-full inline-flex items-center justify-center gap-2 bg-slate-50 hover:bg-slate-100 text-slate-800 font-semibold text-xs py-2.5 rounded-xl border border-slate-200 transition">
                        <i data-lucide="edit" class="w-3.5 h-3.5"></i> Edit Plan
                    </a>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection