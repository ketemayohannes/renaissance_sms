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
        // Get or create permissions
        $viewAcademicReports = Permission::firstOrCreate(['name' => 'view academic reports']);
        $viewReportCards = Permission::firstOrCreate(['name' => 'view report cards']);

        // Assign them to Principal and Vice Principal roles
        $principal = Role::where('name', 'Principal')->first();
        if ($principal) {
            $principal->givePermissionTo([$viewAcademicReports, $viewReportCards]);
        }

        $vicePrincipal = Role::where('name', 'Vice Principal')->first();
        if ($vicePrincipal) {
            $vicePrincipal->givePermissionTo([$viewAcademicReports, $viewReportCards]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $principal = Role::where('name', 'Principal')->first();
        if ($principal) {
            $principal->revokePermissionTo(['view academic reports', 'view report cards']);
        }

        $vicePrincipal = Role::where('name', 'Vice Principal')->first();
        if ($vicePrincipal) {
            $vicePrincipal->revokePermissionTo(['view academic reports', 'view report cards']);
        }
    }
};
