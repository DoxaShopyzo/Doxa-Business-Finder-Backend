<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminTenantController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminPlanController;
use App\Http\Controllers\Admin\AdminCreditController;
use App\Http\Controllers\Admin\AdminSettingsController;
use App\Http\Controllers\Admin\AdminPaymentController;
use App\Http\Controllers\Admin\AdminReportController;
use App\Http\Controllers\Admin\AdminAuditController;
use App\Http\Controllers\Admin\AdminApiUsageController;
use App\Http\Controllers\Admin\AdminSecurityController;
use App\Http\Controllers\Auth\VerificationController;

// Public Concept Preview Route (Website / App Prototype)
Route::get('/preview/{token}', [\App\Http\Controllers\Preview\PreviewController::class, 'renderPreview'])->name('preview.render');

// Direct root URL to Admin Login / Dashboard
Route::get('/', function () {
    if (Auth::check()) {
        $user = Auth::user();
        if ($user->is_super_admin || $user->role === 'super_admin' || $user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }
    }
    return redirect()->route('login');
});

// Admin Web Authentication Routes
Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AdminAuthController::class, 'login']);
Route::any('/logout', [AdminAuthController::class, 'logout'])->name('logout');
Route::get('/admin/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');

// Live Razorpay Checkout Test Interface
Route::get('/razorpay-checkout', function () {
    $keyId = config('services.razorpay.key_id');
    $keySecret = config('services.razorpay.key_secret');
    $api = new \Razorpay\Api\Api($keyId, $keySecret);
    $amount = 500.00;
    $amountPaise = (int)($amount * 100);
    
    $tenant = \App\Models\Tenant::first();
    $user = \App\Models\User::first();
    $package = \App\Models\CreditPackage::first();

    $payment = \App\Models\Payment::create([
        'tenant_id' => $tenant->id,
        'user_id' => $user->id,
        'gateway' => 'razorpay',
        'amount' => $amount,
        'currency' => 'INR',
        'status' => 'pending',
        'type' => 'credit_purchase',
        'reference_type' => \App\Models\CreditPackage::class,
        'reference_id' => $package?->id ?? 1,
    ]);

    $order = $api->order->create([
        'amount' => $amountPaise,
        'currency' => 'INR',
        'receipt' => 'payment_' . $payment->id,
        'notes' => [
            'tenant_id' => $tenant->id,
            'payment_id' => $payment->id,
        ],
    ]);

    $payment->update(['gateway_order_id' => $order['id']]);

    return view('razorpay_test_checkout', [
        'keyId' => $keyId,
        'orderId' => $order['id'],
        'amount' => $amount,
        'amountPaise' => $amountPaise,
    ]);
});

// Signed email verification landing
Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])
    ->middleware(['signed', 'throttle:6,1'])
    ->name('verification.verify');

// Protected Admin Panel Routes
Route::prefix('admin')->middleware(['auth', 'is_super_admin'])->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index']);
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    
    // Tenants
    Route::resource('tenants', AdminTenantController::class);
    Route::post('tenants/{tenant}/suspend', [AdminTenantController::class, 'suspend'])->name('tenants.suspend');
    Route::post('tenants/{tenant}/activate', [AdminTenantController::class, 'activate'])->name('tenants.activate');
    
    // Users & Plans
    Route::resource('users', AdminUserController::class);
    Route::post('users/{id}/force-logout', [AdminUserController::class, 'forceLogout'])->name('users.force-logout');
    Route::post('users/{id}/topup-quota', [AdminUserController::class, 'topupQuota'])->name('users.topup-quota');
    Route::get('users/{id}/sessions', [AdminUserController::class, 'sessions'])->name('users.sessions');
    Route::resource('plans', AdminPlanController::class);
    
    // Credits
    Route::get('/credits', [AdminCreditController::class, 'index'])->name('credits.index');
    Route::get('/credits/export', [AdminCreditController::class, 'export'])->name('credits.export');
    Route::post('/credits/adjust', [AdminCreditController::class, 'adjust'])->name('credits.adjust');
    Route::get('/credits/{tenantId}/transactions', [AdminCreditController::class, 'transactions'])->name('credits.transactions');
    
    // Payments
    Route::get('/payments', [AdminPaymentController::class, 'index'])->name('payments.index');
    Route::get('/payments/export', [AdminPaymentController::class, 'export'])->name('payments.export');
    Route::post('/payments/{id}/refund', [AdminPaymentController::class, 'refund'])->name('payments.refund');
    
    // Reports
    Route::get('/reports', [AdminReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export', [AdminReportController::class, 'export'])->name('reports.export');
    
    // API Usage
    Route::get('/api-usage', [AdminApiUsageController::class, 'index'])->name('api-usage.index');
    Route::get('/api-usage/export', [AdminApiUsageController::class, 'export'])->name('api-usage.export');
    
    // Security & Logins
    Route::get('/security', [AdminSecurityController::class, 'index'])->name('security.index');
    Route::get('/security/export', [AdminSecurityController::class, 'export'])->name('security.export');
    Route::delete('/security/tokens/{id}', [AdminSecurityController::class, 'revokeToken'])->name('security.tokens.revoke');
    
    // Audit Logs
    Route::get('/audit-logs', [AdminAuditController::class, 'index'])->name('audit.index');
    Route::get('/audit-logs/export', [AdminAuditController::class, 'export'])->name('audit.export');
    
    // Settings
    Route::get('/settings', [AdminSettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings', [AdminSettingsController::class, 'update'])->name('settings.update');

    // Preview Management & Templates
    Route::get('/previews/{id}/editor', [\App\Http\Controllers\Preview\PreviewController::class, 'editor'])->name('previews.editor');
    Route::get('/previews/templates', [\App\Http\Controllers\Admin\AdminPreviewTemplateController::class, 'index'])->name('previews.templates.index');
    Route::post('/previews/templates/{id}/toggle', [\App\Http\Controllers\Admin\AdminPreviewTemplateController::class, 'toggleStatus'])->name('previews.templates.toggle');
    Route::post('/previews/templates/{id}/duplicate', [\App\Http\Controllers\Admin\AdminPreviewTemplateController::class, 'duplicate'])->name('previews.templates.duplicate');
    Route::get('/previews/templates/{id}/preview-raw', [\App\Http\Controllers\Admin\AdminPreviewTemplateController::class, 'previewRaw'])->name('previews.templates.preview_raw');
});