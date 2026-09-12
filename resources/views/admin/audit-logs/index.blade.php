@extends('layouts.admin')
@section('title', 'Audit Logs')
@section('content')

{{-- Filters --}}
<div class="bg-white rounded-xl shadow-sm border border-slate-100 mb-6">
    <div class="p-6">
        <form method="GET" action="{{ route('admin.audit.index') }}" class="flex flex-wrap items-end gap-4">
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">Search</label>
                <div class="relative">
                    <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Model, IP address..."
                           class="pl-10 pr-4 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 w-56">
                </div>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">Action</label>
                <select name="action" class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All Actions</option>
                    @foreach($actions as $action)
                    <option value="{{ $action }}" {{ request('action') === $action ? 'selected' : '' }}>{{ ucfirst($action) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">Model Type</label>
                <select name="model_type" class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All Models</option>
                    @foreach($modelTypes as $type)
                    <option value="{{ $type }}" {{ request('model_type') === $type ? 'selected' : '' }}>{{ class_basename($type) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">User</label>
                <select name="user_id" class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All Users</option>
                    @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
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
                <a href="{{ route('admin.audit.index') }}" class="px-4 py-2 rounded-lg text-sm font-medium text-slate-600 hover:bg-slate-100 transition-colors">
                    Reset
                </a>
                <a href="{{ route('admin.audit.export', request()->query()) }}" class="inline-flex items-center px-4 py-2 bg-slate-100 border border-slate-300 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-200 transition">
                    <i data-lucide="download" class="w-4 h-4 mr-1.5"></i> Export CSV
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Audit Logs Table --}}
<div class="bg-white rounded-xl shadow-sm border border-slate-100">
    <div class="p-6 border-b border-slate-100">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-slate-900">Audit Log Entries</h3>
            <span class="text-sm text-slate-500">{{ $logs->total() }} total entries</span>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-slate-100">
                    <th class="text-left py-3 px-6 text-xs font-medium text-slate-500 uppercase tracking-wider w-8"></th>
                    <th class="text-left py-3 px-6 text-xs font-medium text-slate-500 uppercase tracking-wider">User</th>
                    <th class="text-left py-3 px-6 text-xs font-medium text-slate-500 uppercase tracking-wider">Action</th>
                    <th class="text-left py-3 px-6 text-xs font-medium text-slate-500 uppercase tracking-wider">Model</th>
                    <th class="text-left py-3 px-6 text-xs font-medium text-slate-500 uppercase tracking-wider">ID</th>
                    <th class="text-left py-3 px-6 text-xs font-medium text-slate-500 uppercase tracking-wider">IP Address</th>
                    <th class="text-left py-3 px-6 text-xs font-medium text-slate-500 uppercase tracking-wider">Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tbody x-data="{ expanded: false }" class="divide-y divide-slate-50">
                <tr class="hover:bg-slate-50 transition-colors cursor-pointer" @click="expanded = !expanded">
                    <td class="py-3 px-6">
                        <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400 transition-transform" :class="expanded && 'rotate-90'"></i>
                    </td>
                    <td class="py-3 px-6">
                        <p class="text-sm font-medium text-slate-900">{{ $log->user->name ?? 'System' }}</p>
                        <p class="text-xs text-slate-400">{{ $log->user->email ?? '' }}</p>
                    </td>
                    <td class="py-3 px-6">
                        @php
                            $actionColors = [
                                'created' => 'bg-emerald-50 text-emerald-700',
                                'updated' => 'bg-blue-50 text-blue-700',
                                'deleted' => 'bg-red-50 text-red-700',
                                'restored' => 'bg-violet-50 text-violet-700',
                                'login' => 'bg-cyan-50 text-cyan-700',
                                'logout' => 'bg-slate-100 text-slate-700',
                            ];
                            $aColor = $actionColors[$log->action] ?? 'bg-slate-100 text-slate-700';
                        @endphp
                        <span class="text-xs font-medium px-2 py-1 rounded-full {{ $aColor }}">{{ ucfirst($log->action) }}</span>
                    </td>
                    <td class="py-3 px-6 text-sm text-slate-600">{{ $log->model_type ? class_basename($log->model_type) : '-' }}</td>
                    <td class="py-3 px-6 text-sm text-slate-500 font-mono">{{ $log->model_id ?? '-' }}</td>
                    <td class="py-3 px-6 text-sm text-slate-500 font-mono">{{ $log->ip_address ?? '-' }}</td>
                    <td class="py-3 px-6 text-sm text-slate-500">{{ $log->created_at->format('d M Y H:i') }}</td>
                </tr>
                {{-- Expanded Row --}}
                <tr x-show="expanded" x-cloak>
                    <td colspan="7" class="px-6 py-4 bg-slate-50">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                            @if($log->old_values)
                            <div>
                                <h4 class="text-xs font-semibold text-slate-500 uppercase mb-2">Old Values</h4>
                                <pre class="bg-white rounded-lg p-3 text-xs text-slate-700 overflow-x-auto border border-slate-200 max-h-48">{{ json_encode($log->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            </div>
                            @endif
                            @if($log->new_values)
                            <div>
                                <h4 class="text-xs font-semibold text-slate-500 uppercase mb-2">New Values</h4>
                                <pre class="bg-white rounded-lg p-3 text-xs text-slate-700 overflow-x-auto border border-slate-200 max-h-48">{{ json_encode($log->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            </div>
                            @endif
                            @if(!$log->old_values && !$log->new_values)
                            <div class="col-span-2">
                                <p class="text-sm text-slate-400">No change data recorded for this entry.</p>
                            </div>
                            @endif
                            @if($log->user_agent)
                            <div class="col-span-2">
                                <h4 class="text-xs font-semibold text-slate-500 uppercase mb-2">User Agent</h4>
                                <p class="text-xs text-slate-500 break-all">{{ $log->user_agent }}</p>
                            </div>
                            @endif
                        </div>
                    </td>
                </tr>
                </tbody>
                @empty
                <tr>
                    <td colspan="7" class="py-12 text-center">
                        <div class="flex flex-col items-center">
                            <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mb-4">
                                <i data-lucide="scroll-text" class="w-8 h-8 text-slate-300"></i>
                            </div>
                            <p class="text-slate-500 font-medium">No audit logs found</p>
                            <p class="text-sm text-slate-400 mt-1">Audit logs will appear as users perform actions</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($logs->hasPages())
    <div class="px-6 py-4 border-t border-slate-100">
        {{ $logs->links() }}
    </div>
    @endif
</div>

@push('scripts')
<script>lucide.createIcons();</script>
@endpush
@endsection
