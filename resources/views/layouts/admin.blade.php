<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard') — Doxa Business Finder</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style> body { font-family: 'Inter', sans-serif; background-color: #f8fafc; } </style>
</head>
<body x-data="{ sidebarOpen: true }" class="flex h-screen overflow-hidden text-slate-800">
    <!-- Sidebar -->
    <aside :class="sidebarOpen ? 'w-64' : 'w-20'" class="bg-slate-950 text-white flex flex-col transition-all duration-300 select-none">
        <div class="p-4 flex items-center justify-between border-b border-slate-800">
            <span x-show="sidebarOpen" class="text-lg font-bold text-blue-400 flex items-center gap-2">
                <i data-lucide="layers" class="w-6 h-6 text-blue-500"></i> DOXA Admin
            </span>
            <i data-lucide="layers" x-show="!sidebarOpen" class="w-6 h-6 text-blue-500 mx-auto"></i>
            <button @click="sidebarOpen = !sidebarOpen" class="text-slate-400 hover:text-white p-1 rounded-md">
                <i data-lucide="menu" class="w-5 h-5"></i>
            </button>
        </div>

        <nav class="flex-1 overflow-y-auto py-3 space-y-1 px-2 text-sm font-medium">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.dashboard') ? 'bg-blue-600 text-white font-semibold shadow-sm' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                <i data-lucide="layout-dashboard" class="w-5 h-5 mr-3 flex-shrink-0"></i>
                <span x-show="sidebarOpen">Dashboard</span>
            </a>
            <a href="{{ route('admin.tenants.index') }}" class="flex items-center px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.tenants.*') ? 'bg-blue-600 text-white font-semibold shadow-sm' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                <i data-lucide="building-2" class="w-5 h-5 mr-3 flex-shrink-0"></i>
                <span x-show="sidebarOpen">Tenants</span>
            </a>
            <a href="{{ route('admin.users.index') }}" class="flex items-center px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.users.*') ? 'bg-blue-600 text-white font-semibold shadow-sm' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                <i data-lucide="users" class="w-5 h-5 mr-3 flex-shrink-0"></i>
                <span x-show="sidebarOpen">Users</span>
            </a>
            <a href="{{ route('admin.plans.index') }}" class="flex items-center px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.plans.*') ? 'bg-blue-600 text-white font-semibold shadow-sm' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                <i data-lucide="package" class="w-5 h-5 mr-3 flex-shrink-0"></i>
                <span x-show="sidebarOpen">Plans</span>
            </a>
            <a href="{{ route('admin.credits.index') }}" class="flex items-center px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.credits.*') ? 'bg-blue-600 text-white font-semibold shadow-sm' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                <i data-lucide="coins" class="w-5 h-5 mr-3 flex-shrink-0"></i>
                <span x-show="sidebarOpen">Credits & Wallets</span>
            </a>
            <a href="{{ route('admin.payments.index') }}" class="flex items-center px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.payments.*') ? 'bg-blue-600 text-white font-semibold shadow-sm' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                <i data-lucide="credit-card" class="w-5 h-5 mr-3 flex-shrink-0"></i>
                <span x-show="sidebarOpen">Payments</span>
            </a>
            <a href="{{ route('admin.reports.index') }}" class="flex items-center px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.reports.*') ? 'bg-blue-600 text-white font-semibold shadow-sm' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                <i data-lucide="bar-chart-3" class="w-5 h-5 mr-3 flex-shrink-0"></i>
                <span x-show="sidebarOpen">Reports & Analytics</span>
            </a>
            <a href="{{ route('admin.api-usage.index') }}" class="flex items-center px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.api-usage.*') ? 'bg-blue-600 text-white font-semibold shadow-sm' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                <i data-lucide="activity" class="w-5 h-5 mr-3 flex-shrink-0"></i>
                <span x-show="sidebarOpen">API Usage & Spend</span>
            </a>
            <a href="{{ route('admin.security.index') }}" class="flex items-center px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.security.*') ? 'bg-blue-600 text-white font-semibold shadow-sm' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                <i data-lucide="shield-check" class="w-5 h-5 mr-3 flex-shrink-0"></i>
                <span x-show="sidebarOpen">Security & Logins</span>
            </a>
            <a href="{{ route('admin.audit.index') }}" class="flex items-center px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.audit.*') ? 'bg-blue-600 text-white font-semibold shadow-sm' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                <i data-lucide="clipboard-list" class="w-5 h-5 mr-3 flex-shrink-0"></i>
                <span x-show="sidebarOpen">Audit Logs</span>
            </a>
            <a href="{{ route('admin.previews.templates.index') }}" class="flex items-center px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.previews.templates.*') ? 'bg-blue-600 text-white font-semibold shadow-sm' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                <i data-lucide="layout-template" class="w-5 h-5 mr-3 flex-shrink-0"></i>
                <span x-show="sidebarOpen">Preview Templates</span>
            </a>
            <a href="{{ route('admin.settings.index') }}" class="flex items-center px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.settings.*') ? 'bg-blue-600 text-white font-semibold shadow-sm' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                <i data-lucide="settings" class="w-5 h-5 mr-3 flex-shrink-0"></i>
                <span x-show="sidebarOpen">Settings</span>
            </a>
        </nav>
    </aside>

    <!-- Main Content Area -->
    <main class="flex-1 flex flex-col overflow-hidden">
        <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-6">
            <h2 class="text-xl font-bold text-slate-800">@yield('title')</h2>
            <div class="flex items-center space-x-3">
                <span class="text-xs bg-emerald-100 text-emerald-800 font-semibold px-2.5 py-1 rounded-full">Super Admin</span>
                <div class="w-9 h-9 rounded-full bg-slate-900 text-white font-bold flex items-center justify-center text-sm shadow">
                    {{ substr(Auth::user()->name ?? 'A', 0, 1) }}
                </div>
                <a href="{{ route('logout') }}" class="text-xs text-slate-500 hover:text-red-600 flex items-center gap-1 font-medium transition ml-2">
                    <i data-lucide="log-out" class="w-4 h-4"></i> Logout
                </a>
            </div>
        </header>
        <div class="flex-1 overflow-auto p-6 bg-slate-50">
            @if(session('success'))
                <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl flex items-center gap-2">
                    <i data-lucide="check-circle" class="w-5 h-5 text-emerald-600"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl flex items-center gap-2">
                    <i data-lucide="alert-circle" class="w-5 h-5 text-red-600"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif
            @yield('content')
        </div>
    </main>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
        });
    </script>
    @stack('scripts')
</body>
</html>