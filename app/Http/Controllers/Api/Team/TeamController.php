<?php
namespace App\Http\Controllers\Api\Team;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;

class TeamController extends Controller
{
    public function index()
    {
        $members = User::where('tenant_id', auth()->user()->tenant_id)
            ->with('roles')
            ->orderBy('name')
            ->get();
            
        return UserResource::collection($members);
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string',
            'role' => 'required|in:manager,sales_executive,telecaller,viewer',
            'password' => 'required|string|min:8',
        ]);
        
        $tenant = auth()->user()->tenant;
        
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'tenant_id' => $tenant->id,
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
        
        app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->id);
        $user->assignRole($request->role);
        
        return (new UserResource($user))
            ->response()
            ->setStatusCode(201);
    }
    
    public function show(User $team)
    {
        return new UserResource($team->load('roles'));
    }
    
    public function update(Request $request, User $team)
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'phone' => 'nullable|string',
            'role' => 'sometimes|in:manager,sales_executive,telecaller,viewer',
            'status' => 'sometimes|in:active,suspended',
        ]);
        
        $team->update($request->only(['name', 'phone', 'status']));
        
        if ($request->has('role')) {
            app(PermissionRegistrar::class)->setPermissionsTeamId($team->tenant_id);
            $team->syncRoles([$request->role]);
        }
        
        return new UserResource($team->fresh()->load('roles'));
    }
    
    public function destroy(User $team)
    {
        $team->update(['status' => 'suspended']);
        $team->tokens()->delete();
        return response()->json(null, 204);
    }
}