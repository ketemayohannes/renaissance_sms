<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // Added this line

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Categorize Roles
        $academicRoles = ['Principal', 'Vice Principal', 'Supervisor', 'Teacher', 'Assistant Teacher'];
        $adminRoles = ['Senior Finance Officer', 'Junior Finance Officer', 'HR Manager', 'Secretary', 'Librarian', 'Inventory Manager', 'School Nurse', 'Registrar', 'IT / System Admin'];

        foreach ($academicRoles as $roleName) {
            DB::table('roles')->where('name', $roleName)->update(['category' => 'academic']);
        }

        foreach ($adminRoles as $roleName) {
            // Ensure Registrar and IT exist
            $exists = DB::table('roles')->where('name', $roleName)->exists();
            if (!$exists) {
                DB::table('roles')->insert([
                    'name' => $roleName,
                    'guard_name' => 'web',
                    'category' => 'administrative',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            } else {
                DB::table('roles')->where('name', $roleName)->update(['category' => 'administrative']);
            }
        }

        // 2. Migrate existing Academic Data
        $employees = DB::table('employees')->get();
        foreach ($employees as $employee) {
            // If they have academic fields filled, they likely need a detail record
            if ($employee->teacher_rank || $employee->qualification_level || $employee->specialization || $employee->periods_per_week) {
                DB::table('academic_staff_details')->insert([
                    'employee_id' => $employee->id,
                    'teacher_rank' => $employee->teacher_rank,
                    'qualification_level' => $employee->qualification_level,
                    'specialization' => $employee->specialization,
                    'periods_per_week' => $employee->periods_per_week,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('roles')->update(['category' => null]);
    }
};
