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
        $role = Role::firstOrCreate(
            ['name' => 'General Manager', 'guard_name' => 'web'],
            ['category' => 'administrative']
        );

        // Assign high-level permissions similar to Principal
        $permissions = [
            'view users', 'view students', 'view employees',
            'view divisions', 'view grade levels', 'view sections', 'view subjects',
            'view marks', 'view fees', 'view financial reports',
            'view library', 'view health', 'view inventory',
            'send notifications', 'manage notice board', 'access chat'
        ];

        // Ensure permissions exist before syncing
        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        $role->syncPermissions($permissions);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $role = Role::where('name', 'General Manager')->first();
        if ($role) {
            $role->delete();
        }
    }
};
