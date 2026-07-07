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
            // Staff attendance & leave (HR availability module).
            // Deliberately separate from 'manage attendance', which is the STUDENT
            // attendance permission held by teachers/homeroom teachers.
            'manage staff attendance', 'manage leave requests', 'request leave', 'view staff availability',

            // Library
            'view library', 'manage books', 'issue books', 'return books',

            // Communication
            'send notifications', 'manage notice board',
            'access chat', // Basic chat access
            'create group chats',

            // Health Records
            'view health', 'manage health',

            // Inventory
            'view inventory', 'manage inventory',

            // Gradebook (fine-grained)
            'view master gradebook', 'edit master gradebook',
            'view subject gradebook', 'edit subject gradebook',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // 2. Create Roles and Assign Permissions

        // Super Admin
        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin']);
        $superAdmin->syncPermissions(Permission::all());

        // Principal / Admin
        $principal = Role::firstOrCreate(['name' => 'Principal']);
        $principal->syncPermissions([
            'view users', 'view students', 'view employees',
            'view divisions', 'view grade levels', 'view sections', 'view subjects',
            'view marks', 'publish results', 'generate report cards', 'generate transcripts',
            'view fees', 'view financial reports',
            'view library',
            'send notifications', 'manage notice board', 'access chat',
            'view master gradebook', 'edit master gradebook',
            'view subject gradebook', 'edit subject gradebook',
            'view academic reports', 'view report cards',
        ]);

        // Vice Principal
        $vicePrincipal = Role::firstOrCreate(['name' => 'Vice Principal']);
        $vicePrincipal->syncPermissions($principal->permissions);

        // Principal-only HR grants, added AFTER the Vice Principal copy above so the VP
        // does not inherit leave-approval / register powers (approvers are HR Manager,
        // Super Admin, and Principal only). Both still get the read-only availability view.
        $principal->givePermissionTo(['manage staff attendance', 'manage leave requests', 'view staff availability']);
        $vicePrincipal->givePermissionTo(['view staff availability']);

        // Supervisor
        $supervisor = Role::firstOrCreate(['name' => 'Supervisor']);
        $supervisor->syncPermissions([
            'view students', 'view grade levels', 'view sections', 'view subjects',
            'view marks', 'generate report cards', 'access chat', 'manage notice board',
            'view master gradebook', 'view subject gradebook', 'edit subject gradebook',
            'view staff availability',
        ]);

        // Teacher
        $teacher = Role::firstOrCreate(['name' => 'Teacher']);
        $teacher->syncPermissions([
            'view students', 'view subjects', 'view marks', 'enter marks', 'view library', 'access chat',
            'request leave',
        ]);

        // Homeroom Teacher
        $homeroomTeacher = Role::firstOrCreate(['name' => 'Homeroom Teacher']);
        $homeroomTeacher->syncPermissions([
            'view students', 'manage attendance', 'access chat'
        ]);

        // Department Head
        $departmentHead = Role::firstOrCreate(['name' => 'Department Head']);
        $departmentHead->syncPermissions([
            'view employees', 'view students', 'view subjects', 'view marks', 'manage subjects', 'access chat'
        ]);

        // Assistant Teacher
        $asstTeacher = Role::firstOrCreate(['name' => 'Assistant Teacher']);
        $asstTeacher->syncPermissions([
            'view students', 'view subjects', 'view marks', 'enter marks', 'access chat',
            'request leave',
        ]);

        // Senior Finance Officer
        $srFinance = Role::firstOrCreate(['name' => 'Senior Finance Officer']);
        $srFinance->syncPermissions([
            'view students', 'view fees', 'manage fees', 'collect payments', 'view financial reports',
            'view payroll', 'process payroll', 'access chat'
        ]);

        // Junior Finance Officer
        $jrFinance = Role::firstOrCreate(['name' => 'Junior Finance Officer']);
        $jrFinance->syncPermissions([
            'view students', 'view fees', 'collect payments', 'access chat'
        ]);

        // Secretary
        $secretary = Role::firstOrCreate(['name' => 'Secretary']);
        $secretary->syncPermissions([
            'view students', 'view employees', 'send notifications', 'manage notice board', 'access chat'
        ]);

        // School Nurse
        $nurse = Role::firstOrCreate(['name' => 'School Nurse']);
        $nurse->syncPermissions([
            'view students', 'view health', 'manage health', 'access chat'
        ]);

        // Inventory Manager
        $inventory = Role::firstOrCreate(['name' => 'Inventory Manager']);
        $inventory->syncPermissions([
            'view inventory', 'manage inventory', 'access chat'
        ]);

        // HR Manager
        $hr = Role::firstOrCreate(['name' => 'HR Manager']);
        $hr->syncPermissions([
            'view employees', 'manage employees', 'view payroll', 'process payroll', 'manage attendance', 'access chat',
            'manage staff attendance', 'manage leave requests', 'view staff availability',
        ]);

        // Librarian
        $librarian = Role::firstOrCreate(['name' => 'Librarian']);
        $librarian->syncPermissions([
            'view students', 'view employees', 'view library', 'manage books', 'issue books', 'return books', 'access chat', 'manage notice board'
        ]);

        // Support Staff (No permissions assigned here, but roles created)
        Role::firstOrCreate(['name' => 'Accountant']);
        Role::firstOrCreate(['name' => 'Staff']);
        Role::firstOrCreate(['name' => 'Janitor']);
        Role::firstOrCreate(['name' => 'Guard']);

        // Parent
        $parent = Role::firstOrCreate(['name' => 'Parent']);
        $parent->syncPermissions(['access chat']); // Basic, additional handled by custom logic

        // Student
        $student = Role::firstOrCreate(['name' => 'Student']);
        $student->syncPermissions(['access chat', 'view library']);
    }
}
