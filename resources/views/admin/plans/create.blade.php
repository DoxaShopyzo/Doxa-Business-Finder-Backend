@extends('layouts.admin')
@section('title', 'Create Subscription Plan')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Create Subscription Plan</h1>
            <p class="text-slate-500 text-sm mt-1">Configure pricing, duration (28/84 days), and total data collection quota</p>
        </div>
        <a href="{{ route('admin.plans.index') }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900">
            &larr; Back to Plans
        </a>
    </div>

    <div class="bg-white p-8 rounded-2xl border border-slate-200 shadow-sm">
        <form action="{{ route('admin.plans.store') }}" method="POST" class="space-y-5">
            @csrf

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Plan Name *</label>
                <input type="text" name="name" value="{{ old('name') }}" placeholder="e.g. Starter (28 Days), Growth (84 Days)" required
                    class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition">
                @error('name') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Price (INR ₹) *</label>
                    <input type="number" step="0.01" name="price" value="{{ old('price', '499') }}" required
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Billing Cycle *</label>
                    <select name="billing_cycle" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition">
                        <option value="custom" selected>Custom Duration (28 / 84 Days)</option>
                        <option value="monthly">Monthly</option>
                        <option value="yearly">Yearly</option>
                        <option value="one_time">One Time</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Membership Duration (Days) *</label>
                    <div class="flex gap-2 mb-2">
                        <button type="button" onclick="document.getElementById('planDuration').value=28" class="px-3 py-1 text-xs border border-slate-200 rounded-lg hover:bg-slate-50">28 Days</button>
                        <button type="button" onclick="document.getElementById('planDuration').value=84" class="px-3 py-1 text-xs border border-slate-200 rounded-lg hover:bg-slate-50">84 Days</button>
                    </div>
                    <input type="number" id="planDuration" name="duration_days" value="{{ old('duration_days', '28') }}" required min="1" max="365"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Total Data Units Cap (Records) *</label>
                    <div class="flex gap-2 mb-2">
                        <button type="button" onclick="document.getElementById('planQuota').value=5000" class="px-3 py-1 text-xs border border-slate-200 rounded-lg hover:bg-slate-50">5,000</button>
                        <button type="button" onclick="document.getElementById('planQuota').value=15000" class="px-3 py-1 text-xs border border-slate-200 rounded-lg hover:bg-slate-50">15,000</button>
                    </div>
                    <input type="number" id="planQuota" name="max_data_units_total" value="{{ old('max_data_units_total', '5000') }}" required min="1" max="500000"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Credits Included</label>
                    <input type="number" name="credits_included" value="{{ old('credits_included', '500') }}" required
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Concurrent Device Limit</label>
                    <input type="number" name="max_users" value="1" readonly
                        class="w-full bg-slate-100 border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-500">
                    <p class="text-[10px] text-slate-400 mt-1">Locked to 1 active device session</p>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Daily Searches Cap</label>
                    <input type="number" name="max_searches_per_day" value="{{ old('max_searches_per_day', '100') }}" required
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition">
                </div>
            </div>

            <div>
                <label class="flex items-center space-x-2 cursor-pointer">
                    <input type="checkbox" name="reset_daily_limit_on_period" value="1" class="w-4 h-4 rounded text-blue-600 focus:ring-blue-500">
                    <span class="text-xs font-medium text-slate-700">Enforce daily search cap simultaneously with total data collection cap</span>
                </label>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Description</label>
                <textarea name="description" rows="3" placeholder="Plan highlights..."
                    class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition">{{ old('description') }}</textarea>
            </div>

            <div>
                <label class="flex items-center space-x-2 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" checked class="w-4 h-4 rounded text-blue-600 focus:ring-blue-500">
                    <span class="text-sm font-medium text-slate-700">Active Plan</span>
                </label>
            </div>

            <div class="pt-4 flex justify-end gap-3 border-t border-slate-100">
                <a href="{{ route('admin.plans.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition">Cancel</a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-6 py-2.5 rounded-xl shadow-sm transition">Create Plan</button>
            </div>
        </form>
    </div>
</div>
@endsection