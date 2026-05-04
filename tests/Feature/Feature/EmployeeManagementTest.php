<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Employee;
use App\Models\AcademicYear;
use App\Models\SystemCounter;
use App\Services\EmployeeService;
use Tests\TestCase;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;

class EmployeeManagementTest extends TestCase
{
    use RefreshDatabase;

    protected $employeeService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->employeeService = new EmployeeService();
        
        // Seed basic roles
        // Seed basic roles with categories (use firstOrCreate as migrations might have created them)
        Role::firstOrCreate(['name' => 'Teacher', 'guard_name' => 'web'], ['category' => 'academic']);
        Role::firstOrCreate(['name' => 'Secretary', 'guard_name' => 'web'], ['category' => 'administrative']);
        Role::firstOrCreate(['name' => 'Principal', 'guard_name' => 'web'], ['category' => 'academic']);
        
        // Seed academic year
        AcademicYear::create([
            'name' => '2025/26',
            'start_date' => '2025-09-01',
            'end_date' => '2026-06-30',
            'is_active' => true
        ]);
    }

    public function test_can_create_employee_via_service()
    {
        $data = [
            'first_name' => 'John',
            'middle_name' => 'Quincy',
            'last_name' => 'Adams',
            'gender' => 'M',
            'phone' => '0911223344',
            'designation' => 'Teacher',
            'staff_category' => 'academic',
            'basic_salary' => 5000,
            'joining_date' => '2024-01-01',
            'date_of_birth' => '1990-01-01',
        ];

        $employee = $this->employeeService->createEmployee($data);

        $this->assertDatabaseHas('employees', [
            'first_name' => 'John',
            'last_name' => 'Adams',
            'employee_id' => $employee->employee_id
        ]);

        $this->assertDatabaseHas('users', [
            'email' => $employee->email,
            'name' => 'John Quincy Adams'
        ]);

        $this->assertTrue($employee->user->hasRole('Teacher'));
    }

    public function test_can_create_academic_staff_with_details()
    {
        $data = [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'gender' => 'F',
            'phone' => '0911000005',
            'role' => 'Teacher',
            'staff_category' => 'academic',

            'qualification_level' => 'Masters',
            'specialization' => 'Biology',

            'secondary_responsibilities' => 'Lab Coordinator',
            'basic_salary' => 6000,
            'date_of_birth' => '1985-05-15',
        ];

        $employee = $this->employeeService->createEmployee($data);

        $this->assertDatabaseHas('academic_staff_details', [
            'employee_id' => $employee->id,

            'specialization' => 'Biology'
        ]);

        $this->assertEquals('Lab Coordinator', $employee->academicDetails->secondary_responsibilities);
    }

    public function test_can_create_administrative_staff_with_details()
    {
        $data = [
            'first_name' => 'Bob',
            'last_name' => 'Admin',
            'gender' => 'M',
            'phone' => '0911000006',
            'role' => 'Secretary',
            'staff_category' => 'administrative',
            'system_access_roles' => 'Full access to records',
            'basic_salary' => 4000,
            'date_of_birth' => '1992-08-20',
        ];

        $employee = $this->employeeService->createEmployee($data);

        $this->assertDatabaseHas('administrative_staff_details', [
            'employee_id' => $employee->id,
            'system_access_roles' => 'Full access to records'
        ]);
        
        $this->assertNull($employee->academicDetails);
    }

    public function test_can_bulk_import_employees()
    {
        $rows = [
            [
                'first_name' => 'Alice',
                'middle_name' => '',
                'last_name' => 'Smith',
                'email' => 'alice@example.com',
                'gender' => 'F',
                'phone' => '0911000001',
                'designation' => 'Secretary',
                'staff_category' => 'administrative',
                'date_of_birth' => '1990-01-01',
            ],
            [
                'first_name' => 'Bob',
                'middle_name' => '',
                'last_name' => 'Jones',
                'email' => '', // Auto-generate
                'gender' => 'M',
                'phone' => '0911000002',
                'designation' => 'Teacher',
                'staff_category' => 'academic',
                'date_of_birth' => '1992-01-01',
            ]
        ];

        $result = $this->employeeService->bulkImport($rows);

        $this->assertEquals(2, $result['success']);
        $this->assertEquals(0, $result['skipped']);
        $this->assertCount(0, $result['errors']);

        $this->assertDatabaseHas('employees', ['first_name' => 'Alice']);
        $this->assertDatabaseHas('employees', ['first_name' => 'Bob']);
        
        $bob = Employee::where('first_name', 'Bob')->first();
        $this->assertEquals('bob.jones@renaissance.edu.et', $bob->email);
    }

    public function test_handles_duplicate_emails_in_import()
    {
        // Pre-create a user with an email
        User::create([
            'name' => 'Existing User',
            'email' => 'existing@example.com',
            'password' => 'password'
        ]);

        $rows = [
            [
                'first_name' => 'Duplicate',
                'middle_name' => '',
                'last_name' => 'Email',
                'email' => 'existing@example.com', // Conflict
                'gender' => 'M',
                'phone' => '0911000003',
            ]
        ];

        $result = $this->employeeService->bulkImport($rows);

        $this->assertEquals(0, $result['success']);
        $this->assertEquals(1, $result['skipped']);
        $this->assertStringContainsString('email has already been taken', $result['errors'][0]);
    }
}
