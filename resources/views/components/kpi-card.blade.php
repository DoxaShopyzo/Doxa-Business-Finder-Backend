@props(['title', 'value', 'icon', 'trend'])
<div class="bg-white p-6 rounded-lg shadow-sm border border-slate-100 flex items-center justify-between">
    <div>
        <p class="text-sm text-slate-500 mb-1">{{ $title }}</p>
        <h3 class="text-2xl font-bold">{{ $value }}</h3>
        <span class="text-xs text-green-500">{{ $trend }}</span>
    </div>
    <div class="p-3 bg-blue-50 text-blue-600 rounded-full">
        <i data-lucide="{{ $icon }}" class="w-6 h-6"></i>
    </div>
</div>