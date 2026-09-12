<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Tenant;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\UserLoginSession;
use App\Services\SubscriptionService;
use App\Services\CreditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminUserController extends Controller {
    public function index(Request $request) {
        $users = User::with(['tenant', 'activeLoginSession'])
            ->where('is_super_admin', false)
            ->when($request->search, fn($q, $s) => $q->where('name', 'like', "%{$s}%")->orWhere('email', 'like', "%{$s}%")->orWhere('phone', 'like', "%{$s}%"))
            ->when($request->membership_status, function ($q, $status) {
                if ($status === 'active') {
                    $q->where('membership_expires_at', '>', now());
                } elseif ($status === 'expired') {
                    $q->where(function ($sub) {
                        $sub->where('membership_expires_at', '<=', now())
                            ->orWhereNull('membership_expires_at');
                    });
                }
            })
            ->latest()
            ->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    public function create() {
        $tenants = Tenant::where('status', '!=', 'suspended')->get();
        $plans = Plan::where('is_active', true)->orderBy('sort_order')->get();
        return view('admin.users.create', compact('tenants', 'plans'));
    }

    public function store(Request $request) {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:6',
            'role' => 'required|string',
            'plan_id' => 'nullable|exists:plans,id',
            'custom_duration_days' => 'nullable|integer|min:1',
            'custom_data_limit' => 'nullable|integer|min:1',
        ]);

        $tenantId = $request->tenant_id;

        // If no tenant selected, create a company tenant automatically for this member
        if (!$tenantId) {
            $companyName = $request->company_name ?: ($request->name . ' Workspace');
            $tenant = Tenant::create([
                'name' => $companyName,
                'slug' => Str::slug($companyName) . '-' . Str::random(4),
                'company_name' => $companyName,
                'email' => $request->email,
                'phone' => $request->phone,
                'status' => 'active',
            ]);
            $tenantId = $tenant->id;
            app(CreditService::class)->getOrCreateWallet($tenantId);
        } else {
            $tenant = Tenant::findOrFail($tenantId);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'tenant_id' => $tenantId,
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($tenantId);
        $user->assignRole($request->role);

        // Assign Membership Plan if selected
        if ($request->plan_id) {
            $plan = Plan::findOrFail($request->plan_id);
            $durationDays = $request->filled('custom_duration_days') 
                ? (int) $request->custom_duration_days 
                : ($plan->duration_days ?: 28);
            $dataLimit = $request->filled('custom_data_limit') 
                ? (int) $request->custom_data_limit 
                : ($plan->max_data_units_total ?: 5000);

            app(SubscriptionService::class)->subscribe(
                $tenant,
                $plan,
                null,
                $user->id,
                $durationDays,
                $dataLimit
            );
        }

        return redirect()->route('admin.users.index')->with('success', "Member '{$user->name}' created successfully with assigned membership!");
    }

    public function edit($id) {
        $user = User::with(['tenant', 'roles'])->findOrFail($id);
        $tenants = Tenant::where('status', '!=', 'suspended')->get();
        $plans = Plan::where('is_active', true)->get();
        $activeSub = $user->active_subscription;
        return view('admin.users.edit', compact('user', 'tenants', 'plans', 'activeSub'));
    }

    public function update(Request $request, $id) {
        $user = User::findOrFail($id);
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'membership_expires_at' => 'nullable|date',
        ]);

        $user->update($request->only('name', 'email', 'phone'));
        if ($request->password) {
            $user->update(['password' => Hash::make($request->password)]);
        }
        if ($request->filled('membership_expires_at')) {
            $user->update(['membership_expires_at' => $request->membership_expires_at]);
        }

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
    }

    public function forceLogout($id) {
        $user = User::findOrFail($id);

        $user->update([
            'force_logout' => true,
            'active_session_token' => null,
        ]);

        UserLoginSession::where('user_id', $user->id)
            ->where('is_active', true)
            ->update([
                'is_active' => false,
                'logged_out_at' => now(),
                'logout_reason' => 'admin_forced',
            ]);

        try {
            $user->tokens()->delete();
        } catch (\Throwable $e) {}

        return back()->with('success', "Active session for '{$user->name}' force-terminated.");
    }

    public function topupQuota(Request $request, $id) {
        $request->validate([
            'extra_units' => 'required|integer|min:1|max:500000',
        ]);

        $user = User::findOrFail($id);
        $sub = $user->active_subscription;

        if (!$sub) {
            return back()->with('error', "No active subscription found for '{$user->name}' to top up.");
        }

        $currentLimit = $sub->data_limit_total ?? $sub->data_used_total;
        $newLimit = $currentLimit + $request->extra_units;

        $sub->update(['data_limit_total' => $newLimit]);

        return back()->with('success', "Added " . number_format($request->extra_units) . " records to quota! New limit: " . number_format($newLimit));
    }

    public function topupDataQuota(Request $request, $id) {
        return $this->topupQuota($request, $id);
    }

    public function sessions($id) {
        $user = User::with('tenant')->findOrFail($id);
        $sessions = UserLoginSession::where('user_id', $id)
            ->latest('logged_in_at')
            ->paginate(30);

        return view('admin.users.sessions', compact('user', 'sessions'));
    }
}