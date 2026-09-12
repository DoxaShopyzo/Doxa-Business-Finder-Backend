<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard') - Doxa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style> body { font-family: 'Inter', sans-serif; background-color: #f8fafc; } </style>
</head>
<body x-data="{ sidebarOpen: true }" class="flex h-screen overflow-hidden text-slate-800">
    <aside :class="sidebarOpen ? 'w-64' : 'w-20'" class="bg-slate-950 text-white flex flex-col transition-all duration-300">
        <div class="p-4 flex items-center justify-between border-b border-slate-800">
            <span x-show="sidebarOpen" class="text-xl font-bold text-blue-500">DOXA Admin</span>
            <i data-lucide="layers" x-show="!sidebarOpen" class="w-8 h-8 text-blue-500"></i>
            <button @click="sidebarOpen = !sidebarOpen"><i data-lucide="menu" class="w-5 h-5"></i></button>
        </div>
        <nav class="flex-1 overflow-y-auto py-4 space-y-1">
            <!-- Simplified nav -->
            <a href="#" class="flex items-center px-4 py-2 hover:bg-slate-800 text-slate-300 hover:text-white">
                <i data-lucide="layout-dashboard" class="w-5 h-5 mr-3"></i> <span x-show="sidebarOpen">Dashboard</span>
            </a>
            <a href="#" class="flex items-center px-4 py-2 hover:bg-slate-800 text-slate-300 hover:text-white">
                <i data-lucide="users" class="w-5 h-5 mr-3"></i> <span x-show="sidebarOpen">Users</span>
            </a>
        </nav>
    </aside>
    <main class="flex-1 flex flex-col overflow-hidden">
        <header class="h-16 bg-white border-b flex items-center justify-between px-6">
            <h2 class="text-xl font-semibold">@yield('title')</h2>
            <div class="flex items-center space-x-4">
                <button><i data-lucide="bell" class="w-5 h-5 text-slate-500"></i></button>
                <div class="w-8 h-8 rounded-full bg-slate-200"></div>
            </div>
        </header>
        <div class="flex-1 overflow-auto p-6">
            @yield('content')
        </div>
    </main>
    <script> lucide.createIcons(); </script>
    @stack('scripts')
</body>
</html>