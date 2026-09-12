<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verified — Doxa Business Finder</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-xl p-8 text-center border border-slate-100">
        <!-- Success Icon -->
        <div class="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-6 shadow-inner">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
        </div>

        <h1 class="text-2xl font-bold text-slate-800 mb-2">Email Verified Successfully!</h1>
        <p class="text-slate-600 mb-6 text-sm leading-relaxed">
            Thank you for verifying your email address, <strong class="text-slate-800">{{ $user->name }}</strong>. Your account is now fully active.
        </p>

        <div class="bg-slate-50 rounded-xl p-4 mb-6 border border-slate-200/60">
            <p class="text-xs text-slate-500 font-medium">Next Step</p>
            <p class="text-sm font-semibold text-slate-700 mt-1">Open the Doxa Business Finder app to sign in.</p>
        </div>

        <div class="text-xs text-slate-400">
            Doxa Business Finder &copy; {{ date('Y') }} Doxa Infotech Pvt. Ltd.
        </div>
    </div>
</body>
</html>
