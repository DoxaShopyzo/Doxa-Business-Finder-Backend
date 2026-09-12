@extends('layouts.admin')

@section('title', 'Master Preview Templates')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Master Preview Templates</h1>
            <p class="text-sm text-slate-500 mt-1">Manage and inspect pre-designed Website & Mobile App master templates used for instant lead concept generation.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.previews.templates.index') }}" class="px-3.5 py-2 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 rounded-lg text-sm font-medium shadow-sm transition flex items-center gap-2">
                <i data-lucide="refresh-cw" class="w-4 h-4"></i> Reset Filters
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-800 text-sm flex items-center gap-3 shadow-sm">
        <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-600 flex-shrink-0"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    <!-- Metric KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-sm flex items-center gap-3.5">
            <div class="w-11 h-11 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center flex-shrink-0">
                <i data-lucide="layout-grid" class="w-5 h-5"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Templates</p>
                <p class="text-xl font-bold text-slate-900 mt-0.5">{{ $stats['total'] }}</p>
            </div>
        </div>

        <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-sm flex items-center gap-3.5">
            <div class="w-11 h-11 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center flex-shrink-0">
                <i data-lucide="globe" class="w-5 h-5"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Website Templates</p>
                <p class="text-xl font-bold text-indigo-900 mt-0.5">{{ $stats['website_count'] }}</p>
            </div>
        </div>

        <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-sm flex items-center gap-3.5">
            <div class="w-11 h-11 bg-cyan-50 text-cyan-600 rounded-xl flex items-center justify-center flex-shrink-0">
                <i data-lucide="smartphone" class="w-5 h-5"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">App Templates</p>
                <p class="text-xl font-bold text-cyan-900 mt-0.5">{{ $stats['app_count'] }}</p>
            </div>
        </div>

        <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-sm flex items-center gap-3.5">
            <div class="w-11 h-11 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center flex-shrink-0">
                <i data-lucide="check-check" class="w-5 h-5"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Active in Engine</p>
                <p class="text-xl font-bold text-emerald-900 mt-0.5">{{ $stats['active_count'] }}</p>
            </div>
        </div>

        <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-sm flex items-center gap-3.5">
            <div class="w-11 h-11 bg-amber-50 text-amber-600 rounded-xl flex items-center justify-center flex-shrink-0">
                <i data-lucide="sparkles" class="w-5 h-5"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Previews Created</p>
                <p class="text-xl font-bold text-amber-900 mt-0.5">{{ $stats['total_previews_generated'] }}</p>
            </div>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-sm">
        <form method="GET" action="{{ route('admin.previews.templates.index') }}" class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                <input type="text" name="search" value="{{ $search }}" placeholder="Search templates by name, slug or category..." class="w-full pl-10 pr-4 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
            </div>

            <select name="type" class="px-3.5 py-2 border border-slate-200 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                <option value="">All Types (Website & App)</option>
                <option value="website" {{ $typeFilter === 'website' ? 'selected' : '' }}>Websites Only (15)</option>
                <option value="app" {{ $typeFilter === 'app' ? 'selected' : '' }}>Mobile Apps Only (8)</option>
            </select>

            <button type="submit" class="px-4 py-2 bg-slate-900 text-white hover:bg-slate-800 rounded-lg text-sm font-medium transition flex items-center justify-center gap-2">
                <i data-lucide="filter" class="w-4 h-4"></i> Apply Filters
            </button>
        </form>
    </div>

    <!-- Templates Table -->
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50/75 border-b border-slate-200 text-slate-600 font-semibold text-xs uppercase tracking-wider">
                    <tr>
                        <th class="py-3.5 px-4">Template Name & Slug</th>
                        <th class="py-3.5 px-4">Type</th>
                        <th class="py-3.5 px-4">Category Key</th>
                        <th class="py-3.5 px-4">Default Brand Palette</th>
                        <th class="py-3.5 px-4 text-center">Version</th>
                        <th class="py-3.5 px-4 text-center">Previews</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($templates as $tpl)
                    <tr class="hover:bg-slate-50/60 transition">
                        <td class="py-3.5 px-4">
                            <div class="font-semibold text-slate-900">{{ $tpl->name }}</div>
                            <div class="text-xs font-mono text-slate-400 mt-0.5">{{ $tpl->slug }}</div>
                        </td>
                        <td class="py-3.5 px-4 whitespace-nowrap">
                            @if($tpl->type === 'website')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200/60">
                                <i data-lucide="globe" class="w-3.5 h-3.5"></i> Website
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-cyan-50 text-cyan-700 border border-cyan-200/60">
                                <i data-lucide="smartphone" class="w-3.5 h-3.5"></i> App Mockup
                            </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 whitespace-nowrap">
                            <span class="inline-block px-2.5 py-1 bg-slate-100 text-slate-700 rounded-md font-mono text-xs">
                                {{ $tpl->category_key }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="flex items-center gap-1.5">
                                <span class="w-4 h-4 rounded-full border border-slate-300 shadow-xs" style="background-color: {{ $tpl->default_palette['primary'] ?? '#1E3A5F' }};" title="Primary: {{ $tpl->default_palette['primary'] ?? '' }}"></span>
                                <span class="w-4 h-4 rounded-full border border-slate-300 shadow-xs" style="background-color: {{ $tpl->default_palette['secondary'] ?? '#0F172A' }};" title="Secondary: {{ $tpl->default_palette['secondary'] ?? '' }}"></span>
                                <span class="w-4 h-4 rounded-full border border-slate-300 shadow-xs" style="background-color: {{ $tpl->default_palette['accent'] ?? '#0EA5E9' }};" title="Accent: {{ $tpl->default_palette['accent'] ?? '' }}"></span>
                                <span class="w-4 h-4 rounded-full border border-slate-300 shadow-xs" style="background-color: {{ $tpl->default_palette['bg'] ?? '#F8FAFC' }};" title="Background: {{ $tpl->default_palette['bg'] ?? '' }}"></span>
                            </div>
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="text-xs font-mono px-2 py-0.5 bg-slate-100 rounded text-slate-600">v{{ $tpl->version ?? '1.0' }}</span>
                        </td>
                        <td class="py-3.5 px-4 text-center font-semibold text-slate-800">
                            {{ $tpl->previews_count ?? 0 }}
                        </td>
                        <td class="py-3.5 px-4 text-center whitespace-nowrap">
                            @if($tpl->is_active)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                                Active
                            </span>
                            @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">
                                Inactive
                            </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-right whitespace-nowrap">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.previews.templates.preview_raw', $tpl->id) }}" target="_blank" class="p-1.5 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-md transition" title="Preview Sample Design">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                </a>

                                <form action="{{ route('admin.previews.templates.duplicate', $tpl->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="p-1.5 text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded-md transition" title="Duplicate Template">
                                        <i data-lucide="copy" class="w-4 h-4"></i>
                                    </button>
                                </form>

                                <form action="{{ route('admin.previews.templates.toggle', $tpl->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="p-1.5 {{ $tpl->is_active ? 'text-amber-600 hover:text-amber-800 hover:bg-amber-50' : 'text-emerald-600 hover:text-emerald-800 hover:bg-emerald-50' }} rounded-md transition" title="{{ $tpl->is_active ? 'Deactivate' : 'Activate' }}">
                                        <i data-lucide="{{ $tpl->is_active ? 'toggle-right' : 'toggle-left' }}" class="w-4 h-4"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-10 text-slate-400">
                            <i data-lucide="layers" class="w-8 h-8 mx-auto text-slate-300 mb-2"></i>
                            No master templates found matching your search.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($templates->hasPages())
        <div class="p-4 border-t border-slate-200">
            {{ $templates->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
