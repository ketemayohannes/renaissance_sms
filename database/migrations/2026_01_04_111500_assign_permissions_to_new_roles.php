<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Assign permissions to Registrar
        $registrar = Role::where('name', 'Registrar')->first();
        if ($registrar) {
            $perms = [
                'view students', 'create students', 'edit students',
                'view grade levels', 'view sections', 'view subjects',
                'manage grade levels', 'manage sections', 'manage subjects',
                'access chat'
            ];
            
            foreach ($perms as $perm) {
                Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
            }

            $registrar->syncPermissions($perms);
        }

        // 2. Assign permissions to IT / System Admin
        $itAdmin = Role::where('name', 'IT / System Admin')->first();
        if ($itAdmin) {
            // Ensure some base permissions exist if we want to sync all
            // In a real scenario, RolePermissionSeeder should have run
            $itAdmin->syncPermissions(Permission::all());
        }

        // 3. Assign IT / System Admin role to the admin user if it exists
        $adminEmail = 'admin@renaissance.edu.et';
        $adminUser = \App\Models\User::where('email', $adminEmail)->first();
        if ($adminUser && $itAdmin) {
            $adminUser->assignRole($itAdmin);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No major undo needed other than maybe removing roles, but we'll leave them as they are structural now.
    }
};
