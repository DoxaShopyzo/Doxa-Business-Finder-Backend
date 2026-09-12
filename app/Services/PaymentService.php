<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\CreditPackage;
use App\Models\Subscription;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    protected $razorpay;

    public function __construct()
    {
        $keyId = config('services.razorpay.key_id', '');
        $keySecret = config('services.razorpay.key_secret', '');

        if (!empty($keyId) && !empty($keySecret)) {
            try {
                $this->razorpay = new \Razorpay\Api\Api($keyId, $keySecret);
            } catch (\Exception $e) {
                Log::warning('Razorpay SDK init failed, using mock mode', ['error' => $e->getMessage()]);
            }
        }
    }

    /**
     * Create a payment order
     */
    public function createOrder($user, float $amount, string $type, $reference = null): array
    {
        $tenant = $user->tenant;

        // Create Payment record
        $payment = Payment::create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'gateway' => config('doxa.payment.gateway', 'razorpay'),
            'amount' => $amount,
            'currency' => 'INR',
            'status' => 'pending',
            'type' => $type,
            'reference_type' => $reference ? get_class($reference) : null,
            'reference_id' => $reference?->id,
        ]);

        // Create Razorpay order or mock
        if ($this->razorpay) {
            try {
                $order = $this->razorpay->order->create([
                    'amount' => (int) ($amount * 100), // paise
                    'currency' => 'INR',
                    'receipt' => 'payment_' . $payment->id,
                    'notes' => [
                        'tenant_id' => $tenant->id,
                        'type' => $type,
                        'payment_id' => $payment->id,
                    ],
                ]);

                $payment->update(['gateway_order_id' => $order->id]);

                return [
                    'payment_id' => $payment->id,
                    'order_id' => $order->id,
                    'amount' => $amount,
                    'currency' => 'INR',
                    'key_id' => config('services.razorpay.key_id'),
                ];
            } catch (\Exception $e) {
                $payment->update(['status' => 'failed']);
                throw $e;
            }
        }

        // Mock mode
        $mockOrderId = 'order_mock_' . uniqid();
        $payment->update(['gateway_order_id' => $mockOrderId]);

        return [
            'payment_id' => $payment->id,
            'order_id' => $mockOrderId,
            'amount' => $amount,
            'currency' => 'INR',
            'key_id' => 'rzp_test_mock',
            'mock' => true,
        ];
    }

    /**
     * Verify payment signature
     */
    public function verifyPayment(string $paymentId, string $orderId, string $signature): Payment
    {
        $payment = Payment::where('gateway_order_id', $orderId)->firstOrFail();

        if ($this->razorpay) {
            try {
                $attributes = [
                    'razorpay_order_id' => $orderId,
                    'razorpay_payment_id' => $paymentId,
                    'razorpay_signature' => $signature,
                ];
                $this->razorpay->utility->verifyPaymentSignature($attributes);
            } catch (\Exception $e) {
                $payment->update(['status' => 'failed']);
                throw new \App\Exceptions\PaymentVerificationException('Payment verification failed: ' . $e->getMessage());
            }
        }

        $payment->update([
            'gateway_payment_id' => $paymentId,
            'gateway_signature' => $signature,
            'status' => 'success',
            'paid_at' => now(),
        ]);

        // Process post-payment actions
        $this->processPaymentSuccess($payment);

        return $payment;
    }

    /**
     * Handle webhook from Razorpay
     */
    public function processWebhook(array $payload): void
    {
        $event = $payload['event'] ?? '';

        match ($event) {
            'payment.captured' => $this->handlePaymentCaptured($payload),
            'payment.failed' => $this->handlePaymentFailed($payload),
            'order.paid' => $this->handleOrderPaid($payload),
            default => Log::info('Unhandled webhook event', ['event' => $event]),
        };
    }

    protected function processPaymentSuccess(Payment $payment): void
    {
        if ($payment->type === 'credit_purchase') {
            $this->processCreditPurchase($payment);
        } elseif ($payment->type === 'subscription') {
            $this->processSubscriptionPayment($payment);
        }
    }

    protected function processCreditPurchase(Payment $payment): void
    {
        $package = CreditPackage::find($payment->reference_id);
        if (!$package) return;

        $totalCredits = $package->credits + ($package->bonus_credits ?? 0);

        app(CreditService::class)->addCredits(
            $payment->tenant_id,
            $totalCredits,
            'purchase',
            'Credit package: ' . $package->name,
            $payment
        );
    }

    protected function processSubscriptionPayment(Payment $payment): void
    {
        $subscription = Subscription::find($payment->reference_id);
        if (!$subscription) return;

        $subscription->update([
            'status' => 'active',
            'payment_id' => $payment->id,
        ]);
    }

    protected function handlePaymentCaptured(array $payload): void
    {
        $rpPaymentId = $payload['payload']['payment']['entity']['id'] ?? null;
        $orderId = $payload['payload']['payment']['entity']['order_id'] ?? null;

        if ($orderId) {
            $payment = Payment::where('gateway_order_id', $orderId)->first();
            if ($payment && $payment->status !== 'success') {
                $payment->update([
                    'gateway_payment_id' => $rpPaymentId,
                    'status' => 'success',
                    'paid_at' => now(),
                ]);
                $this->processPaymentSuccess($payment);
            }
        }
    }

    protected function handlePaymentFailed(array $payload): void
    {
        $orderId = $payload['payload']['payment']['entity']['order_id'] ?? null;
        if ($orderId) {
            Payment::where('gateway_order_id', $orderId)->update(['status' => 'failed']);
        }
    }

    protected function handleOrderPaid(array $payload): void
    {
        // Already handled by payment.captured in most cases
    }
}
