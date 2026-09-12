<?php
namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\Tenant;
use App\Models\User;
use App\Models\CreditWallet;
use App\Models\Subscription;
use App\Models\Plan;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;

class RegisterController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $validated = $request->validated();
        
        // Create tenant
        $tenant = Tenant::create([
            'name' => $validated['company_name'] ?? $validated['name'],
            'slug' => Str::slug($validated['company_name'] ?? $validated['name']) . '-' . Str::random(5),
            'company_name' => $validated['company_name'] ?? $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'status' => 'trial',
        ]);
        
        // Create user
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'tenant_id' => $tenant->id,
            'phone' => $validated['phone'] ?? null,
            'status' => 'active',
            'email_verified_at' => null,
        ]);
        
        // Assign role
        app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->id);
        $user->assignRole('tenant_owner');

        // Send email verification OTP
        $otp = \App\Http\Controllers\Auth\VerificationController::generateAndSendOtp($user);
        
        // Create credit wallet with 100 free welcome credits
        CreditWallet::create([
            'tenant_id' => $tenant->id,
            'user_id' => null,
            'balance' => 100,
            'total_earned' => 100,
            'total_spent' => 0,
        ]);
        
        // Create trial subscription
        $freePlan = Plan::where('slug', 'free')->first();
        if ($freePlan) {
            Subscription::create([
                'tenant_id' => $tenant->id,
                'plan_id' => $freePlan->id,
                'status' => 'trial',
                'starts_at' => now(),
                'expires_at' => now()->addDays(config('doxa.subscription.trial_days', 14)),
            ]);
            
            // Add free credits
            if ($freePlan->credits_included > 0) {
                $wallet = CreditWallet::where('tenant_id', $tenant->id)->whereNull('user_id')->first();
                $wallet->increment('balance', $freePlan->credits_included);
                $wallet->increment('total_earned', $freePlan->credits_included);
            }
        }
        
        // Generate token
        $token = $user->createToken($validated['device_name'] ?? 'default')->plainTextToken;
        
        return (new UserResource($user))
            ->additional([
                'token' => $token,
                'tenant' => new \App\Http\Resources\TenantResource($tenant),
                'debug_otp' => config('app.debug') ? $otp : null,
                'message' => 'Registration successful! A 6-digit verification code has been sent to your email.',
            ])
            ->response()
            ->setStatusCode(201);
    }
}