<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminTenantController extends Controller {

    public function index(Request $request) {
        $query = Tenant::withCount('users')
            ->when($request->search, fn($q, $s) => $q->where('company_name', 'like', "%{$s}%")->orWhere('email', 'like', "%{$s}%"))
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->latest();

        $tenants = $query->paginate(20);
        return view('admin.tenants.index', compact('tenants'));
    }

    public function create() {
        $plans = Plan::where('is_active', true)->get();
        return view('admin.tenants.create', compact('plans'));
    }

    public function store(Request $request) {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8',
            'plan_id' => 'required|exists:plans,id',
        ]);

        $tenant = Tenant::create([
            'name' => $request->company_name,
            'slug' => Str::slug($request->company_name) . '-' . Str::random(4),
            'company_name' => $request->company_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'status' => 'active',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'tenant_id' => $tenant->id,
            'status' => 'active',
        ]);

        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($tenant->id);
        $user->assignRole('tenant_owner');

        // Create wallet and subscription
        app(\App\Services\CreditService::class)->getOrCreateWallet($tenant->id);
        $plan = Plan::find($request->plan_id);
        app(\App\Services\SubscriptionService::class)->subscribe($tenant, $plan);

        return redirect()->route('admin.tenants.index')->with('success', "Tenant '{$tenant->company_name}' created!");
    }

    public function edit($id) {
        $tenant = Tenant::with('users')->findOrFail($id);
        return view('admin.tenants.edit', compact('tenant'));
    }

    public function update(Request $request, $id) {
        $tenant = Tenant::findOrFail($id);
        $request->validate([
            'company_name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'nullable|string|max:20',
        ]);

        $tenant->update($request->only('company_name', 'email', 'phone'));
        $tenant->update(['name' => $request->company_name]);

        return redirect()->route('admin.tenants.index')->with('success', 'Tenant updated.');
    }

    public function suspend($id) {
        $tenant = Tenant::findOrFail($id);
        $tenant->update(['status' => 'suspended']);
        return redirect()->route('admin.tenants.index')->with('success', "'{$tenant->company_name}' suspended.");
    }

    public function activate($id) {
        $tenant = Tenant::findOrFail($id);
        $tenant->update(['status' => 'active']);
        return redirect()->route('admin.tenants.index')->with('success', "'{$tenant->company_name}' activated.");
    }
}