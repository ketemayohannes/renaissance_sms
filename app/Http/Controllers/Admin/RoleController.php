<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $roles = Role::with('permissions')->get();
        return view('admin.roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // PERFORMANCE: Cache grouped permissions
        $permissions = \Illuminate\Support\Facades\Cache::remember('grouped_permissions_by_name', 3600, function() {
            return Permission::all()->groupBy(function($data) {
                return explode(' ', $data->name)[1] ?? 'Other';
            });
        });
        return view('admin.roles.create', compact('permissions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:roles,name',
            'permissions' => 'array'
        ]);

        $role = Role::create(['name' => $request->name]);
        
        if($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        return redirect()->route('admin.roles.index')->with('success', 'Role created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $role = Role::findById($id);
        // PERFORMANCE: Cache grouped permissions
        $permissions = \Illuminate\Support\Facades\Cache::remember('grouped_permissions_by_name', 3600, function() {
            return Permission::all()->groupBy(function($data) {
                return explode(' ', $data->name)[1] ?? 'Other';
            });
        });
        $rolePermissions = $role->permissions->pluck('name')->toArray();

        return view('admin.roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|unique:roles,name,'.$id,
            'permissions' => 'array'
        ]);

        $role = Role::findById($id);
        $role->update(['name' => $request->name]);

        if($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        return redirect()->route('admin.roles.index')->with('success', 'Role updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $role = Role::findById($id);
        if($role->name === 'Super Admin') {
            return back()->with('error', 'Cannot delete Super Admin role.');
        }
        $role->delete();
        return redirect()->route('admin.roles.index')->with('success', 'Role deleted successfully.');
    }
}
