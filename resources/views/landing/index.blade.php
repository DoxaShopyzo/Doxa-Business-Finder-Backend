<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doxa Business Finder</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="font-[Inter] bg-slate-50 text-slate-900">
    <header class="bg-slate-950 text-white py-20 px-8 text-center">
        <h1 class="text-5xl font-bold mb-6">Discover Businesses. Identify Opportunities. Convert More Leads.</h1>
        <div class="space-x-4">
            <a href="{{ url('/admin/dashboard') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium shadow-md transition">Admin Portal</a>
            <a href="#pricing" class="bg-white hover:bg-slate-100 text-slate-900 px-6 py-3 rounded-lg font-medium border border-slate-200 transition">View Plans</a>
        </div>
    </header>
    <section class="py-20 px-8 max-w-6xl mx-auto">
        <h2 class="text-3xl font-bold text-center mb-12">How it Works</h2>
        <div class="grid grid-cols-4 gap-8 text-center">
            <div><div class="text-2xl font-bold text-blue-600 mb-2">1</div><h3 class="font-semibold">Search</h3></div>
            <div><div class="text-2xl font-bold text-blue-600 mb-2">2</div><h3 class="font-semibold">Score</h3></div>
            <div><div class="text-2xl font-bold text-blue-600 mb-2">3</div><h3 class="font-semibold">CRM</h3></div>
            <div><div class="text-2xl font-bold text-blue-600 mb-2">4</div><h3 class="font-semibold">Convert</h3></div>
        </div>
    </section>
</body>
</html>