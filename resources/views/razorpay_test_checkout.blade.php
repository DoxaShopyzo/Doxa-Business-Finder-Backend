<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doxa Business Finder - Razorpay Live Checkout Test</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-slate-800 rounded-2xl p-6 border border-slate-700 shadow-2xl">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-10 h-10 rounded-xl bg-sky-500/20 text-sky-400 flex items-center justify-center font-bold text-xl">D</div>
            <div>
                <h1 class="font-bold text-lg text-white">Doxa Business Finder</h1>
                <p class="text-xs text-slate-400">Live Razorpay Test Payment</p>
            </div>
        </div>

        <div class="bg-slate-900/60 rounded-xl p-4 border border-slate-700/50 mb-6 space-y-2 text-sm">
            <div class="flex justify-between">
                <span class="text-slate-400">Package:</span>
                <span class="font-semibold text-white">Growth Pack (120 Credits)</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-400">Order ID:</span>
                <span class="font-mono text-sky-400 text-xs">{{ $orderId }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-400">Amount:</span>
                <span class="font-bold text-emerald-400">₹{{ number_format($amount, 2) }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-400">Key ID:</span>
                <span class="font-mono text-xs text-slate-400">{{ substr($keyId, 0, 12) }}...</span>
            </div>
        </div>

        <div class="bg-amber-500/10 border border-amber-500/20 rounded-lg p-3 text-xs text-amber-300 mb-6">
            <div class="font-semibold mb-1">Razorpay Test Card:</div>
            <div>Card: <span class="font-mono bg-amber-500/20 px-1 rounded">4111 1111 1111 1111</span></div>
            <div>Exp: <span class="font-mono">12/28</span> | CVV: <span class="font-mono">123</span> | OTP: <span class="font-mono">123456</span></div>
        </div>

        <button id="rzp-button" class="w-full py-3.5 px-4 bg-sky-500 hover:bg-sky-400 text-slate-950 font-bold rounded-xl shadow-lg transition-all duration-150 flex items-center justify-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            Pay with Razorpay
        </button>

        <div id="payment-success" class="hidden mt-4 p-4 bg-emerald-500/20 border border-emerald-500/40 rounded-xl text-center">
            <div class="text-emerald-400 font-bold mb-1">Payment Successful!</div>
            <p class="text-xs text-slate-300 mb-2">Razorpay's servers are now dispatching the webhook to ngrok.</p>
            <div id="payment-id" class="font-mono text-xs text-emerald-300"></div>
        </div>
    </div>

    <script>
    var options = {
        "key": "{{ $keyId }}",
        "amount": "{{ $amountPaise }}",
        "currency": "INR",
        "name": "Doxa Business Finder",
        "description": "Growth Pack - 120 Credits",
        "order_id": "{{ $orderId }}",
        "handler": function (response){
            document.getElementById('rzp-button').classList.add('hidden');
            document.getElementById('payment-success').classList.remove('hidden');
            document.getElementById('payment-id').innerText = 'Payment ID: ' + response.razorpay_payment_id + '\nOrder ID: ' + response.razorpay_order_id;
        },
        "prefill": {
            "name": "Doxa Test User",
            "email": "test@doxainfotech.com",
            "contact": "9876543210"
        },
        "theme": {
            "color": "#0EA5E9"
        }
    };
    var rzp = new Razorpay(options);
    document.getElementById('rzp-button').onclick = function(e){
        rzp.open();
        e.preventDefault();
    }
    </script>
</body>
</html>
