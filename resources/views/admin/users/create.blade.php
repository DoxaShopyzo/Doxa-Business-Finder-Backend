@extends('layouts.admin')
@section('title', 'Create Membership User')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Create Membership User</h1>
            <p class="text-slate-500 text-sm mt-1">Register a paid member with single-device login, custom duration (28/84 days), and data quota</p>
        </div>
        <a href="{{ route('admin.users.index') }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900">
            &larr; Back to Users
        </a>
    </div>

    <div class="bg-white p-8 rounded-2xl border border-slate-200 shadow-sm">
        <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-5">
            @csrf

            <!-- User Credentials -->
            <div class="border-b border-slate-100 pb-4">
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <i data-lucide="user" class="w-4 h-4 text-blue-600"></i> Member Credentials (Login Info)
                </h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Full Name / Member Name *</label>
                        <input type="text" name="name" value="{{ old('name') }}" placeholder="e.g. Rajesh Kumar" required
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition">
                        @error('name') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Email Address *</label>
                            <input type="email" name="email" value="{{ old('email') }}" placeholder="rajesh@gmail.com" required
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition">
                            @error('email') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Mobile Number (Login Identifier)</label>
                            <input type="text" name="phone" value="{{ old('phone') }}" placeholder="9840012345"
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition">
                            @error('phone') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">Password *</label>
                            <button type="button" onclick="generatePassword()" class="text-xs text-blue-600 hover:text-blue-800 font-semibold flex items-center gap-1">
                                <i data-lucide="key" class="w-3 h-3"></i> Generate Password
                            </button>
                        </div>
                        <input type="text" id="passwordInput" name="password" value="{{ old('password') }}" placeholder="Admin sets password" required
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-mono focus:outline-none focus:border-blue-500 focus:bg-white transition">
                        @error('password') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <!-- Membership Plan & Duration -->
            <div class="border-b border-slate-100 pb-4">
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <i data-lucide="clock" class="w-4 h-4 text-emerald-600"></i> Paid Membership Plan & Duration
                </h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Select Membership Plan *</label>
                        <select name="plan_id" id="planSelect" onchange="onPlanChange()" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition">
                            <option value="">-- No Plan (Unsubscribed / Trial) --</option>
                            @foreach($plans as $plan)
                                <option value="{{ $plan->id }}" 
                                    data-duration="{{ $plan->duration_days ?: 28 }}"
                                    data-quota="{{ $plan->max_data_units_total ?: 5000 }}"
                                    {{ old('plan_id') == $plan->id ? 'selected' : '' }}>
                                    {{ $plan->name }} — ₹{{ number_format($plan->price, 0) }} ({{ $plan->duration_days ?: 28 }} Days | {{ number_format($plan->max_data_units_total ?: 5000) }} Records)
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Membership Duration (Days) *</label>
                            <input type="number" id="customDurationInput" name="custom_duration_days" value="{{ old('custom_duration_days', 28) }}" min="1" max="365"
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition">
                            <p class="text-[11px] text-slate-400 mt-1">Defaults to 28 or 84 days based on selected plan</p>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Total Data Collection Limit (Records)</label>
                            <input type="number" id="customQuotaInput" name="custom_data_limit" value="{{ old('custom_data_limit', 5000) }}" min="1" max="500000"
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition">
                            <p class="text-[11px] text-slate-400 mt-1">Total businesses user can collect in this duration</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Organization & Workspace -->
            <div>
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <i data-lucide="building" class="w-4 h-4 text-indigo-600"></i> Workspace & Access Role
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Company / Workspace Name</label>
                        <input type="text" name="company_name" value="{{ old('company_name') }}" placeholder="Auto-created if empty"
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Role *</label>
                        <select name="role" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition">
                            <option value="tenant_owner" selected>Business Owner (Full Access)</option>
                            <option value="manager">Manager</option>
                            <option value="staff">Sales Staff</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="pt-4 flex justify-end gap-3 border-t border-slate-100">
                <a href="{{ route('admin.users.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition">Cancel</a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-6 py-2.5 rounded-xl shadow-sm transition flex items-center gap-2">
                    <i data-lucide="check" class="w-4 h-4"></i> Create & Activate Membership
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function generatePassword() {
    const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789!@#$';
    let pwd = '';
    for (let i = 0; i < 10; i++) {
        pwd += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    document.getElementById('passwordInput').value = pwd;
}

function onPlanChange() {
    const sel = document.getElementById('planSelect');
    const opt = sel.options[sel.selectedIndex];
    if (opt && opt.value) {
        const duration = opt.getAttribute('data-duration');
        const quota = opt.getAttribute('data-quota');
        if (duration) document.getElementById('customDurationInput').value = duration;
        if (quota) document.getElementById('customQuotaInput').value = quota;
    }
}
</script>
@endsection