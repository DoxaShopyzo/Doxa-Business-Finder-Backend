@extends('layouts.admin')
@section('title', 'Onboard New Tenant')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Onboard Tenant</h1>
            <p class="text-slate-500 text-sm mt-1">Register a new company and create its primary admin user</p>
        </div>
        <a href="{{ route('admin.tenants.index') }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900">
            &larr; Back to Tenants
        </a>
    </div>

    <div class="bg-white p-8 rounded-2xl border border-slate-200 shadow-sm">
        <form action="{{ route('admin.tenants.store') }}" method="POST" class="space-y-5">
            @csrf

            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider border-b border-slate-100 pb-2">Company Details</h3>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Company Name *</label>
                <input type="text" name="company_name" value="{{ old('company_name') }}" placeholder="e.g. Acme Corp" required
                    class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition">
                @error('company_name') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Subscription Plan *</label>
                <select name="plan_id" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition">
                    @foreach($plans as $plan)
                        <option value="{{ $plan->id }}" {{ old('plan_id') == $plan->id ? 'selected' : '' }}>
                            {{ $plan->name }} (₹{{ number_format($plan->price, 0) }} - {{ number_format($plan->credits_included) }} credits)
                        </option>
                    @endforeach
                </select>
                @error('plan_id') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider border-b border-slate-100 pb-2 pt-4">Primary Admin User</h3>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Admin Full Name *</label>
                <input type="text" name="name" value="{{ old('name') }}" placeholder="e.g. John Doe" required
                    class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition">
                @error('name') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Admin Email *</label>
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="admin@company.com" required
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition">
                    @error('email') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" placeholder="+91 98400 00000"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Temporary Password *</label>
                <input type="password" name="password" required
                    class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition">
                @error('password') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="pt-4 flex justify-end gap-3">
                <a href="{{ route('admin.tenants.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition">Cancel</a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-6 py-2.5 rounded-xl shadow-sm transition">Onboard Tenant</button>
            </div>
        </form>
    </div>
</div>
@endsection