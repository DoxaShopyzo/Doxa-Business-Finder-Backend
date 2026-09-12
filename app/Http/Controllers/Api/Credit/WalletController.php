<?php
namespace App\Http\Controllers\Api\Credit;

use App\Http\Controllers\Controller;
use App\Http\Requests\PurchaseCreditRequest;
use App\Http\Resources\CreditWalletResource;
use App\Http\Resources\CreditTransactionResource;
use App\Models\CreditPackage;
use App\Services\CreditService;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function __construct(
        protected CreditService $creditService,
        protected PaymentService $paymentService
    ) {}
    
    public function show()
    {
        $user = auth()->user();
        $tenantId = (int)($user->tenant_id ?? $user->id ?? 1);
        $wallet = $this->creditService->getOrCreateWallet($tenantId, null);
        
        return new CreditWalletResource($wallet);
    }
    
    public function transactions(Request $request)
    {
        $user = auth()->user();
        $tenantId = (int)($user->tenant_id ?? $user->id ?? 1);
        $transactions = $this->creditService->getTransactions(
            $tenantId,
            $request->type,
            $request->per_page ?? 20
        );
        
        return CreditTransactionResource::collection($transactions);
    }

    public function packages()
    {
        $packages = CreditPackage::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('price')
            ->get();
            
        return response()->json([
            'success' => true,
            'data' => $packages,
        ]);
    }
    
    public function purchase(PurchaseCreditRequest $request)
    {
        $validated = $request->validated();
        $package = CreditPackage::findOrFail($validated['package_id']);
        
        $order = $this->paymentService->createOrder(
            auth()->user(),
            $package->price,
            'credit_purchase',
            $package
        );
        
        return response()->json([
            'order_id' => $order['order_id'] ?? null,
            'amount' => $package->price,
            'currency' => 'INR',
            'package' => $package,
            'key_id' => config('services.razorpay.key_id'),
        ]);
    }
}