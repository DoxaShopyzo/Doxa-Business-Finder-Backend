@extends('layouts.admin')
@section('title', 'Edit Plan')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Edit Plan: {{ $plan->name }}</h1>
            <p class="text-slate-500 text-sm mt-1">Update pricing, duration, and total data collection quota</p>
        </div>
        <a href="{{ route('admin.plans.index') }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900">
            &larr; Back to Plans
        </a>
    </div>

    <div class="bg-white p-8 rounded-2xl border border-slate-200 shadow-sm">
        <form action="{{ route('admin.plans.update', $plan->id) }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Plan Name *</label>
                <input type="text" name="name" value="{{ old('name', $plan->name) }}" required
                    class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition">
                @error('name') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Price (INR ₹) *</label>
                    <input type="number" step="0.01" name="price" value="{{ old('price', $plan->price) }}" required
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Billing Cycle *</label>
                    <select name="billing_cycle" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition">
                        <option value="custom" {{ $plan->billing_cycle === 'custom' ? 'selected' : '' }}>Custom Duration (28 / 84 Days)</option>
                        <option value="monthly" {{ $plan->billing_cycle === 'monthly' ? 'selected' : '' }}>Monthly</option>
                        <option value="yearly" {{ $plan->billing_cycle === 'yearly' ? 'selected' : '' }}>Yearly</option>
                        <option value="one_time" {{ $plan->billing_cycle === 'one_time' ? 'selected' : '' }}>One Time</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Membership Duration (Days) *</label>
                    <input type="number" name="duration_days" value="{{ old('duration_days', $plan->duration_days ?: 28) }}" required min="1" max="365"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Total Data Units Cap (Records)</label>
                    <input type="number" name="max_data_units_total" value="{{ old('max_data_units_total', $plan->max_data_units_total ?: 5000) }}" min="1" max="500000"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Credits Included</label>
                    <input type="number" name="credits_included" value="{{ old('credits_included', $plan->credits_included) }}" required
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Daily Searches Cap</label>
                    <input type="number" name="max_searches_per_day" value="{{ old('max_searches_per_day', $plan->max_searches_per_day) }}" required
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition">
                </div>
            </div>

            <div>
                <label class="flex items-center space-x-2 cursor-pointer">
                    <input type="checkbox" name="reset_daily_limit_on_period" value="1" {{ $plan->reset_daily_limit_on_period ? 'checked' : '' }} class="w-4 h-4 rounded text-blue-600 focus:ring-blue-500">
                    <span class="text-xs font-medium text-slate-700">Enforce daily search cap simultaneously with total data collection cap</span>
                </label>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Description</label>
                <textarea name="description" rows="3"
                    class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition">{{ old('description', $plan->description) }}</textarea>
            </div>

            <div>
                <label class="flex items-center space-x-2 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" {{ $plan->is_active ? 'checked' : '' }} class="w-4 h-4 rounded text-blue-600 focus:ring-blue-500">
                    <span class="text-sm font-medium text-slate-700">Active Plan</span>
                </label>
            </div>

            <div class="pt-4 flex justify-end gap-3 border-t border-slate-100">
                <a href="{{ route('admin.plans.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition">Cancel</a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-6 py-2.5 rounded-xl shadow-sm transition">Save Changes</button>
            </div>
        </form>
    </div>
</div>
@endsection