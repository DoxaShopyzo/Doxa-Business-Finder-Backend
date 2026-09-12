<?php
namespace App\Http\Controllers\Api\Payment;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentResource;
use App\Models\Payment;
use App\Models\WebhookLog;
use App\Services\PaymentService;
use App\Notifications\PaymentSuccessNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService
    ) {}
    
    public function createOrder(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'type' => 'required|in:subscription,credit_purchase',
            'reference_id' => 'required|integer',
        ]);
        
        $order = $this->paymentService->createOrder(
            auth()->user(),
            $request->amount,
            $request->type,
            null
        );
        
        return response()->json($order);
    }
    
    /**
     * Frontend verify endpoint — acts as a status check only.
     * Activation is done strictly via the server-side webhook.
     */
    public function verify(Request $request)
    {
        $request->validate([
            'payment_id' => 'nullable|string',
            'order_id' => 'required|string',
            'signature' => 'nullable|string',
        ]);
        
        $payment = Payment::where('gateway_order_id', $request->order_id)->firstOrFail();
        
        return response()->json([
            'payment' => new PaymentResource($payment),
            'status' => $payment->status,
            'is_paid' => $payment->status === 'success',
        ]);
    }
    
    /**
     * Public Webhook endpoint for Razorpay
     */
    public function webhook(Request $request)
    {
        $rawContent = $request->getContent();
        $payload = json_decode($rawContent, true) ?? $request->all();
        $eventId = $payload['event_id'] ?? $payload['id'] ?? ('wh_' . uniqid());
        $signature = $request->header('X-Razorpay-Signature') ?? $request->header('x-razorpay-signature');
        $webhookSecret = config('services.razorpay.webhook_secret') ?: env('RAZORPAY_WEBHOOK_SECRET', 'test_secret_doxa_12345');

        // 1. Raw Payload Logging before processing
        $log = WebhookLog::create([
            'event_id' => $eventId,
            'gateway' => 'razorpay',
            'event_type' => $payload['event'] ?? 'unknown',
            'payload' => $payload,
            'processed' => false,
        ]);

        // 2. Strict mandatory signature verification (fail-closed)
        if (empty($webhookSecret) || empty($signature)) {
            Log::warning('Razorpay Webhook: Missing secret or signature header', ['event_id' => $eventId]);
            return response()->json(['error' => 'Missing webhook secret or X-Razorpay-Signature header'], 400);
        }

        $expectedSignature = hash_hmac('sha256', $rawContent, $webhookSecret);
        if (!hash_equals($expectedSignature, $signature)) {
            Log::error('Razorpay Webhook: Invalid signature verification failed', [
                'event_id' => $eventId,
                'received_sig' => $signature,
            ]);
            return response()->json(['error' => 'Invalid signature verification failed'], 400);
        }

        // 3. Process the webhook event & activate credits/subscriptions
        try {
            $this->paymentService->processWebhook($payload);
            $log->update(['processed' => true]);
            return response()->json(['status' => 'success', 'message' => 'Webhook processed successfully'], 200);
        } catch (\Exception $e) {
            Log::error('Razorpay Webhook Processing Error', ['error' => $e->getMessage(), 'event_id' => $eventId]);
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}