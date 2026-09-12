<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Http\Resources\TenantResource;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show()
    {
        $user = auth()->user()->load(['tenant', 'roles']);
        return (new UserResource($user))
            ->additional(['tenant' => new TenantResource($user->tenant)]);
    }
    
    public function update(Request $request)
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'phone' => 'nullable|string',
            'avatar' => 'nullable|image|max:2048',
        ]);
        
        $user = auth()->user();
        $data = $request->only(['name', 'phone']);
        
        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $data['avatar'] = $path;
        }
        
        $user->update($data);
        return new UserResource($user->fresh());
    }
    
    public function updateSettings(Request $request)
    {
        $request->validate(['settings' => 'required|array']);
        
        $user = auth()->user();
        $tenant = $user->tenant;
        
        if ($request->has('settings.tenant') && $user->isTenantOwner()) {
            $tenant->update(['settings' => array_merge(
                $tenant->settings ?? [],
                $request->input('settings.tenant')
            )]);
        }
        
        if ($request->has('settings.user')) {
            $user->update(['settings' => array_merge(
                $user->settings ?? [],
                $request->input('settings.user')
            )]);
        }
        
        return response()->json(['message' => 'Settings updated successfully']);
    }
}