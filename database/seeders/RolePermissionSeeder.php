<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Define Permissions
        $permissions = [
            // User Management
            'view users', 'create users', 'edit users', 'delete users',
            'manage roles', // For dynamic role management

            // Academic Structure
            'view divisions', 'manage divisions',
            'view grade levels', 'manage grade levels',
            'view sections', 'manage sections',
            'view subjects', 'manage subjects',

            // Students
            'view students', 'create students', 'edit students', 'delete students',
            'promote students', // Promotion module

            // Grading
            'view marks', 'enter marks', 'publish results',
            'configure assessments', // Dynamic assessment setup
            'generate report cards',
            'generate transcripts',

            // Finance
            'view fees', 'manage fees', 'collect payments', 'view financial reports',

            // HR & Payroll
            'view employees', 'manage employees',
            'view payroll', 'process payroll',
            'manage attendance',

            // Library
            'view library', 'manage books', 'issue books', 'return books',

            // Communication
            'send notifications', 'manage notice board',
            'access chat', // Basic chat access
            'create group chats',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // 2. Create Roles and Assign Permissions

        // Super Admin
        $superAdmin = Role::create(['name' => 'Super Admin']);
        $superAdmin->givePermissionTo(Permission::all());

        // Principal / Admin
        $principal = Role::create(['name' => 'Principal']);
        $principal->givePermissionTo([
            'view users', 'view students', 'view employees',
            'view divisions', 'view grade levels', 'view sections', 'view subjects',
            'view marks', 'publish results', 'generate report cards', 'generate transcripts',
            'view fees', 'view financial reports',
            'view library',
            'send notifications', 'manage notice board', 'access chat'
        ]);

        // Teacher
        $teacher = Role::create(['name' => 'Teacher']);
        $teacher->givePermissionTo([
            'view students', // Only assigned students (handled by policy)
            'view subjects',
            'view marks', 'enter marks',
            'view library',
            'access chat'
        ]);

        // Accountant
        $accountant = Role::create(['name' => 'Accountant']);
        $accountant->givePermissionTo([
            'view students',
            'view fees', 'manage fees', 'collect payments', 'view financial reports',
            'view payroll', 'process payroll', // Often accountants handle payroll too
            'access chat'
        ]);

        // HR Manager
        $hr = Role::create(['name' => 'HR Manager']);
        $hr->givePermissionTo([
            'view employees', 'manage employees',
            'view payroll', 'process payroll',
            'manage attendance',
            'access chat'
        ]);

        // Librarian
        $librarian = Role::create(['name' => 'Librarian']);
        $librarian->givePermissionTo([
            'view students', 'view employees',
            'view library', 'manage books', 'issue books', 'return books',
            'access chat', 'manage notice board'
        ]);

        // Parent
        $parent = Role::create(['name' => 'Parent']);
        $parent->givePermissionTo([
            'view marks', // Own child only
            'view fees', // Own child only
            'access chat'
        ]);

        // Student
        $student = Role::create(['name' => 'Student']);
        $student->givePermissionTo([
            'view marks', // Own only
            'view library',
            'access chat'
        ]);
    }
}
