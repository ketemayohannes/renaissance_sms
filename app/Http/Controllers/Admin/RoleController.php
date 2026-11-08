<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

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
        $permissions = $this->groupedPermissions();
        $descriptions = $this->permissionDescriptions();

        return view('admin.roles.create', compact('permissions', 'descriptions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:roles,name',
            'permissions' => 'array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        $role = Role::create(['name' => $request->name]);
        $role->syncPermissions($request->input('permissions', []));

        return redirect()->route('admin.roles.index')->with('success', 'Role created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $role = Role::findById($id);
        $this->ensureCanEdit($role);

        $permissions = $this->groupedPermissions();
        $descriptions = $this->permissionDescriptions();
        $rolePermissions = $role->permissions->pluck('name')->toArray();

        return view('admin.roles.edit', compact('role', 'permissions', 'descriptions', 'rolePermissions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $role = Role::findById($id);
        $this->ensureCanEdit($role);

        $request->validate([
            'name' => 'required|unique:roles,name,'.$id,
            'permissions' => 'array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        // The privileged roles are referenced by name throughout the app; renaming one
        // would silently strip its holders of their access.
        if (in_array($role->name, User::PRIVILEGED_ROLES, true) && $request->name !== $role->name) {
            return back()->withInput()->with('error', "The {$role->name} role cannot be renamed.");
        }

        $role->update(['name' => $request->name]);

        // Unticking every box must remove every permission, so always sync (an empty
        // form sends no "permissions" field at all).
        $role->syncPermissions($request->input('permissions', []));

        return redirect()->route('admin.roles.index')->with('success', 'Role updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $role = Role::findById($id);
        if (in_array($role->name, User::PRIVILEGED_ROLES, true)) {
            return back()->with('error', "Cannot delete the {$role->name} role.");
        }
        $role->delete();

        return redirect()->route('admin.roles.index')->with('success', 'Role deleted successfully.');
    }

    /**
     * Only a Super Admin / IT / System Admin may change a privileged role.
     */
    private function ensureCanEdit(Role $role): void
    {
        if (in_array($role->name, User::PRIVILEGED_ROLES, true) && ! auth()->user()->hasPrivilegedRole()) {
            abort(403, 'Only a Super Admin or IT / System Admin can change this role.');
        }
    }

    /**
     * Permissions grouped by feature, in the order of config/permissions.php. Anything in
     * the database but not in the feature map goes last, under the "unused" heading.
     *
     * @return Collection<string, Collection<int, Permission>>
     */
    private function groupedPermissions(): Collection
    {
        $all = Permission::orderBy('name')->get()->keyBy('name');
        $groups = collect();

        foreach (config('permissions.groups', []) as $group => $perms) {
            $inGroup = collect(array_keys($perms))
                ->map(fn ($name) => $all->pull($name))
                ->filter()
                ->values();

            if ($inGroup->isNotEmpty()) {
                $groups->put($group, $inGroup);
            }
        }

        if ($all->isNotEmpty()) {
            $groups->put(config('permissions.unused_group', 'Other'), $all->values());
        }

        return $groups;
    }

    /**
     * @return array<string, string> permission name => what it unlocks
     */
    private function permissionDescriptions(): array
    {
        return collect(config('permissions.groups', []))->collapse()->all();
    }
}
