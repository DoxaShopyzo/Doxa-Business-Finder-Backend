@extends('layouts.admin')
@section('title', 'Tenants')
@section('content')
<div class="flex items-center justify-between mb-6">
    <h2 class="text-2xl font-bold text-slate-900">Tenants</h2>
    <a href="{{ route('admin.tenants.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 transition">+ New Tenant</a>
</div>

@if(session('success'))
<div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg mb-6">{{ session('success') }}</div>
@endif

<form method="GET" class="flex gap-3 mb-6">
    <input name="search" value="{{ request('search') }}" placeholder="Search company or email..." class="flex-1 px-4 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
    <select name="status" class="px-4 py-2 border border-slate-200 rounded-lg text-sm">
        <option value="">All Status</option>
        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
        <option value="trial" {{ request('status') === 'trial' ? 'selected' : '' }}>Trial</option>
        <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
    </select>
    <button class="bg-slate-100 text-slate-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-slate-200">Filter</button>
</form>

<div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 border-b border-slate-100">
            <tr>
                <th class="text-left px-6 py-3 font-medium text-slate-500">Company</th>
                <th class="text-left px-6 py-3 font-medium text-slate-500">Email</th>
                <th class="text-center px-6 py-3 font-medium text-slate-500">Users</th>
                <th class="text-center px-6 py-3 font-medium text-slate-500">Status</th>
                <th class="text-left px-6 py-3 font-medium text-slate-500">Created</th>
                <th class="text-right px-6 py-3 font-medium text-slate-500">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
            @forelse($tenants as $tenant)
            <tr class="hover:bg-slate-25">
                <td class="px-6 py-4 font-medium text-slate-900">{{ $tenant->company_name }}</td>
                <td class="px-6 py-4 text-slate-600">{{ $tenant->email }}</td>
                <td class="px-6 py-4 text-center text-slate-600">{{ $tenant->users_count }}</td>
                <td class="px-6 py-4 text-center">
                    <span class="text-xs font-medium px-2 py-1 rounded-full
                        {{ $tenant->status === 'active' ? 'bg-emerald-50 text-emerald-700' : ($tenant->status === 'trial' ? 'bg-amber-50 text-amber-700' : 'bg-red-50 text-red-700') }}">
                        {{ ucfirst($tenant->status) }}
                    </span>
                </td>
                <td class="px-6 py-4 text-slate-500">{{ $tenant->created_at->format('d M Y') }}</td>
                <td class="px-6 py-4 text-right space-x-2">
                    <a href="{{ route('admin.tenants.edit', $tenant) }}" class="text-blue-600 hover:text-blue-800 text-xs font-medium">Edit</a>
                    @if($tenant->status !== 'suspended')
                    <form method="POST" action="{{ route('admin.tenants.suspend', $tenant) }}" class="inline">@csrf <button class="text-red-600 hover:text-red-800 text-xs font-medium" onclick="return confirm('Suspend this tenant?')">Suspend</button></form>
                    @else
                    <form method="POST" action="{{ route('admin.tenants.activate', $tenant) }}" class="inline">@csrf <button class="text-emerald-600 hover:text-emerald-800 text-xs font-medium">Activate</button></form>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="px-6 py-12 text-center text-slate-400">No tenants found</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $tenants->withQueryString()->links() }}</div>
@endsection