<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\ForgotPasswordController;
use App\Http\Controllers\Api\Payment\PaymentController;
use App\Http\Controllers\Api\Subscription\PlanController;
use App\Http\Controllers\Api\Onboarding\OnboardingController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\Team\TeamController;
use App\Http\Controllers\Api\Business\SearchController;
use App\Http\Controllers\Api\Business\BusinessController;
use App\Http\Controllers\Api\Crm\LeadController;
use App\Http\Controllers\Api\Crm\FollowUpController;
use App\Http\Controllers\Api\Crm\QuotationController;
use App\Http\Controllers\Api\Crm\DealController;
use App\Http\Controllers\Api\Credit\WalletController;
use App\Http\Controllers\Api\Report\DashboardController;
use App\Http\Controllers\Api\NotificationController;

// Public auth routes — strict rate limiting (5 per minute)
Route::middleware('throttle:5,1')->group(function () {
    Route::post('/register', [RegisterController::class, 'register']);
    Route::post('/login', [LoginController::class, 'login']);
    Route::post('/auth/resend-verification', [\App\Http\Controllers\Auth\VerificationController::class, 'resend']);
    Route::post('/auth/send-otp', [\App\Http\Controllers\Auth\VerificationController::class, 'resend']);
    Route::post('/auth/verify-otp', [\App\Http\Controllers\Auth\VerificationController::class, 'verifyOtp']);
});

// Password recovery routes
Route::middleware('throttle:5,1')->group(function () {
    Route::post('/forgot-password', [ForgotPasswordController::class, 'forgotPassword']);
    Route::post('/verify-otp', [ForgotPasswordController::class, 'verifyOtp']);
    Route::post('/reset-password', [ForgotPasswordController::class, 'resetPassword']);
});

// Webhook — no auth, no throttle, signature verified in controller
Route::post('/payment/webhook', [PaymentController::class, 'webhook']);
Route::post('/webhook/razorpay', [PaymentController::class, 'webhook']);

// Public read — moderate throttle
Route::middleware('throttle:30,1')->group(function () {
    Route::get('/plans', [PlanController::class, 'index']);
});

// Authenticated routes — 60 requests/minute
Route::middleware(['auth:sanctum', 'device.session', 'tenant.access', 'throttle:60,1'])->group(function () {
    Route::post('/logout', [LoginController::class, 'logout']);
    Route::post('/onboarding', [OnboardingController::class, 'store']);
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::put('/settings', [ProfileController::class, 'updateSettings']);
    Route::apiResource('team', TeamController::class);
    
    // Search — subscription required
    Route::middleware(['check.subscription'])->group(function () {
        Route::post('/search/businesses', [SearchController::class, 'search']);
        Route::get('/search/history', [SearchController::class, 'history']);
        Route::get('/search/{id}', [SearchController::class, 'show']);
        Route::get('/business/{id}', [BusinessController::class, 'show']);
    });
    
    // AI Lead Analyzer Module
    Route::prefix('campaigns')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\Campaign\CampaignController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\Api\Campaign\CampaignController::class, 'store']);
        Route::get('/{id}', [\App\Http\Controllers\Api\Campaign\CampaignController::class, 'show']);
        Route::get('/{id}/leads', [\App\Http\Controllers\Api\Campaign\CampaignController::class, 'leads']);
        Route::get('/{id}/verification-queue', [\App\Http\Controllers\Api\Campaign\CampaignController::class, 'verificationQueue']);
        Route::post('/{id}/leads/{leadId}/verify', [\App\Http\Controllers\Api\Campaign\CampaignController::class, 'verifyLead']);
        Route::post('/{id}/leads/bulk-verify', [\App\Http\Controllers\Api\Campaign\CampaignController::class, 'bulkVerify']);
        Route::get('/{id}/export', [\App\Http\Controllers\Api\Campaign\CampaignController::class, 'export']);
        Route::post('/{id}/reuse-template', [\App\Http\Controllers\Api\Campaign\CampaignController::class, 'reuseTemplate']);
    });

    Route::prefix('ai-analyzer')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Api\Campaign\AiAnalyzerDashboardController::class, 'dashboard']);
        Route::get('/scoring-weights', [\App\Http\Controllers\Api\Campaign\AiAnalyzerDashboardController::class, 'getScoringWeights']);
        Route::put('/scoring-weights', [\App\Http\Controllers\Api\Campaign\AiAnalyzerDashboardController::class, 'updateScoringWeights']);
    });

    // Sales Preview Generation & WhatsApp Delivery
    Route::prefix('previews')->group(function () {
        Route::post('/generate', [\App\Http\Controllers\Preview\PreviewController::class, 'generate']);
        Route::get('/lead/{leadType}/{leadId}', [\App\Http\Controllers\Preview\PreviewController::class, 'getLeadPreviews']);
        Route::post('/{id}/update', [\App\Http\Controllers\Preview\PreviewController::class, 'updatePreview']);
        Route::post('/{id}/switch-template', [\App\Http\Controllers\Preview\PreviewController::class, 'switchTemplate']);
        Route::post('/{id}/log-send', [\App\Http\Controllers\Preview\PreviewController::class, 'logSend']);
        Route::get('/templates', [\App\Http\Controllers\Preview\PreviewController::class, 'listTemplates']);
    });
    
    // CRM
    Route::get('/leads/pipeline', [LeadController::class, 'pipeline']);
    Route::get('/leads/export', [LeadController::class, 'export']);
    Route::apiResource('leads', LeadController::class);
    Route::post('/leads/{lead}/assign', [LeadController::class, 'assign']);
    Route::post('/leads/{lead}/activity', [LeadController::class, 'addActivity']);
    Route::get('/leads/{lead}/activities', [LeadController::class, 'activities']);
    Route::patch('/leads/{lead}/status', [LeadController::class, 'updateStatus']);
    
    Route::apiResource('followups', FollowUpController::class);
    
    Route::apiResource('quotations', QuotationController::class);
    Route::post('/quotations/{quotation}/send', [QuotationController::class, 'send']);
    Route::post('/quotations/{quotation}/accept', [QuotationController::class, 'accept']);
    Route::post('/quotations/{quotation}/reject', [QuotationController::class, 'reject']);
    
    Route::apiResource('deals', DealController::class)->only(['index', 'store']);
    
    // Credits
    Route::get('/credits', [WalletController::class, 'show']);
    Route::get('/credits/transactions', [WalletController::class, 'transactions']);
    Route::get('/credits/packages', [WalletController::class, 'packages']);
    Route::post('/credits/purchase', [WalletController::class, 'purchase']);
    
    // Subscription
    Route::post('/subscription', [PlanController::class, 'subscribe']);
    Route::get('/subscription', [PlanController::class, 'currentSubscription']);
    
    // Payment
    Route::post('/payment/create-order', [PaymentController::class, 'createOrder']);
    Route::post('/payment/verify', [PaymentController::class, 'verify']);
    
    // Reports
    Route::prefix('reports')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index']);
        Route::get('/leads', [DashboardController::class, 'leads']);
        Route::get('/sales', [DashboardController::class, 'sales']);
        Route::get('/usage', [DashboardController::class, 'usage']);
        Route::get('/staff', [DashboardController::class, 'staff']);
    });
    
    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::put('/notifications/{id}/read', [NotificationController::class, 'markRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead']);
});