@extends('layouts.admin')
@section('title', 'System Settings & Compliance')
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

<div class="mb-6 bg-white rounded-xl shadow-sm border border-slate-200 p-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
        <h2 class="text-base font-bold text-slate-800">Configuration Scope</h2>
        <p class="text-xs text-slate-500 mt-0.5">Switch between global system defaults and per-tenant parameter overrides</p>
    </div>
    <form method="GET" action="{{ route('admin.settings.index') }}" class="flex items-center gap-3">
        <select name="tenant_id" onchange="this.form.submit()" class="border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 font-medium">
            <option value="">⚙️ Global System Defaults (All Tenants)</option>
            @foreach($tenants as $t)
                <option value="{{ $t->id }}" {{ $selectedTenantId == $t->id ? 'selected' : '' }}>🏢 Tenant: {{ $t->company_name }}</option>
            @endforeach
        </select>
    </form>
</div>

<form method="POST" action="{{ route('admin.settings.update') }}">
    @csrf
    @method('PUT')
    <input type="hidden" name="tenant_id" value="{{ $selectedTenantId }}">

    <div class="space-y-6">
        @forelse($grouped as $group => $settings)
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-5 border-b border-slate-100 bg-slate-50/50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center text-blue-600">
                        <i data-lucide="{{ $groupIcons[$group] ?? 'settings' }}" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">{{ $groupLabels[$group] ?? ucfirst($group) }}</h3>
                        <p class="text-xs text-slate-500">Settings and constants for {{ $group }}</p>
                    </div>
                </div>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    @foreach($settings as $setting)
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-start py-3 {{ !$loop->last ? 'border-b border-slate-100' : '' }}">
                        <div>
                            <label for="setting_{{ $setting->id }}" class="block text-sm font-semibold text-slate-800">
                                {{ str_replace('_', ' ', ucwords(str_replace('_', ' ', $setting->key))) }}
                            </label>
                            @php $descKey = $group . '.' . $setting->key; @endphp
                            @if(isset($descriptions[$descKey]))
                            <p class="text-xs text-slate-500 mt-1 leading-relaxed">{{ $descriptions[$descKey] }}</p>
                            @endif
                        </div>
                        <div class="md:col-span-2">
                            <div class="flex items-center gap-3">
                                @php
                                    $val = is_array($setting->value) ? json_encode($setting->value) : ($setting->value ?? '');
                                @endphp
                                @if(in_array(strtolower($val), ['true', 'false']))
                                <select name="settings[{{ $setting->id }}]" id="setting_{{ $setting->id }}"
                                        class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="true" {{ strtolower($val) === 'true' ? 'selected' : '' }}>Enabled (true)</option>
                                    <option value="false" {{ strtolower($val) === 'false' ? 'selected' : '' }}>Disabled (false)</option>
                                </select>
                                @elseif(strlen($val) > 80)
                                <textarea name="settings[{{ $setting->id }}]" id="setting_{{ $setting->id }}" rows="2"
                                          class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono">{{ $val }}</textarea>
                                @else
                                <input type="text" name="settings[{{ $setting->id }}]" id="setting_{{ $setting->id }}" value="{{ $val }}"
                                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                @endif
                                <span class="text-xs text-slate-400 font-mono shrink-0 hidden md:block">{{ $setting->key }}</span>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @empty
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-12 text-center">
            <div class="flex flex-col items-center">
                <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mb-4">
                    <i data-lucide="settings" class="w-8 h-8 text-slate-300"></i>
                </div>
                <p class="text-slate-500 font-medium">No settings found for this scope</p>
            </div>
        </div>
        @endforelse
    </div>

    @if($grouped->isNotEmpty())
    <div class="mt-6 flex items-center justify-end gap-4">
        <a href="{{ route('admin.settings.index', ['tenant_id' => $selectedTenantId]) }}" class="px-6 py-2.5 rounded-lg text-sm font-medium text-slate-600 hover:bg-slate-100 transition-colors border border-slate-200">
            Reset
        </a>
        <button type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2.5 px-6 rounded-lg text-sm transition-colors flex items-center shadow-sm">
            <i data-lucide="save" class="w-4 h-4 mr-2"></i>
            Save Settings
        </button>
    </div>
    @endif
</form>

@push('scripts')
<script>lucide.createIcons();</script>
@endpush
@endsection