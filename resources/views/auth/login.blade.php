<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Doxa Business Finder</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-900 via-slate-800 to-indigo-950 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <!-- Brand Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-tr from-sky-500 to-indigo-600 shadow-lg shadow-sky-500/30 mb-4">
                <i class="bi bi-geo-alt-fill text-3xl text-white"></i>
            </div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Doxa Business Finder</h1>
            <p class="text-slate-400 text-sm mt-1">Super Admin Control Center</p>
        </div>

        <!-- Login Card -->
        <div class="bg-slate-800/80 backdrop-blur-xl border border-slate-700/60 rounded-2xl p-8 shadow-2xl">
            <div class="mb-6">
                <h2 class="text-xl font-bold text-white">Sign In</h2>
                <p class="text-slate-400 text-xs mt-1">Enter your administrative credentials to continue</p>
            </div>

            @if ($errors->any())
                <div class="mb-5 bg-rose-500/10 border border-rose-500/30 rounded-xl p-3.5 flex items-start space-x-3 text-rose-300 text-sm">
                    <i class="bi bi-exclamation-triangle-fill text-rose-400 text-base mt-0.5"></i>
                    <div>
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                </div>
            @endif

            @if (session('success'))
                <div class="mb-5 bg-emerald-500/10 border border-emerald-500/30 rounded-xl p-3.5 flex items-center space-x-3 text-emerald-300 text-sm">
                    <i class="bi bi-check-circle-fill text-emerald-400 text-base"></i>
                    <p>{{ session('success') }}</p>
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Admin Email</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
                            <i class="bi bi-envelope"></i>
                        </span>
                        <input type="email" name="email" value="{{ old('email', 'admin@doxainfoplus.com') }}" required autofocus
                            class="w-full bg-slate-900/60 border border-slate-700 text-white text-sm rounded-xl pl-10 pr-4 py-3 focus:outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-500 transition placeholder-slate-500"
                            placeholder="admin@doxainfoplus.com">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Password</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
                            <i class="bi bi-lock"></i>
                        </span>
                        <input type="password" name="password" required
                            class="w-full bg-slate-900/60 border border-slate-700 text-white text-sm rounded-xl pl-10 pr-4 py-3 focus:outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-500 transition placeholder-slate-500"
                            placeholder="••••••••">
                    </div>
                </div>

                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded bg-slate-900 border-slate-700 text-sky-600 focus:ring-sky-500">
                        <span class="text-xs text-slate-400">Remember this session</span>
                    </label>
                </div>

                <button type="submit"
                    class="w-full mt-2 bg-gradient-to-r from-sky-500 to-blue-600 hover:from-sky-400 hover:to-blue-500 text-white font-semibold text-sm py-3 px-4 rounded-xl shadow-lg shadow-sky-500/25 transition duration-150 flex items-center justify-center space-x-2">
                    <span>Sign In to Dashboard</span>
                    <i class="bi bi-arrow-right"></i>
                </button>
            </form>
        </div>

        <!-- Footer -->
        <p class="text-center text-xs text-slate-500 mt-6">
            &copy; {{ date('Y') }} Doxa Infotech Pvt. Ltd. All rights reserved.
        </p>
    </div>
</body>
</html>