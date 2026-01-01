<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\User;
use App\Models\SystemCounter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class EmployeeService
{
    /**
     * Bulk import employees from structured data.
     */
    public function bulkImport(array $rows): array
    {
        $successCount = 0;
        $skippedCount = 0;
        $errors = [];
        
        $defaultPassword = Hash::make('staff1234');
        $year = date('Y');

        foreach ($rows as $index => $row) {
            $rowNum = $index + 2; // +1 for header, +1 for 0-indexing

            try {
                // Basic validation for required fields
                if (empty($row['first_name']) || empty($row['last_name'])) {
                    throw new \Exception("First Name and Last Name/Grandfather's Name are required.");
                }

                if (empty($row['email'])) {
                    throw new \Exception("Email is required for staff account creation.");
                }

                DB::beginTransaction();

                // 1. Check Existence (by Email or Employee ID if provided)
                if (User::where('email', $row['email'])->exists() || Employee::where('email', $row['email'])->exists()) {
                    throw new \Exception("User or Employee with email '{$row['email']}' already exists.");
                }

                if (!empty($row['employee_id']) && Employee::where('employee_id', $row['employee_id'])->exists()) {
                    throw new \Exception("Employee ID '{$row['employee_id']}' already exists.");
                }

                // 2. Create User
                $fullName = trim("{$row['first_name']} {$row['middle_name']} {$row['last_name']}");
                $user = User::create([
                    'name' => $fullName,
                    'email' => $row['email'],
                    'password' => $defaultPassword,
                ]);

                // Assign role based on staff category
                $role = 'Staff';
                if ($row['staff_category'] === 'academic') {
                    $role = 'Teacher';
                }
                $user->assignRole($role);

                // 3. Generate Employee ID if not provided
                $employeeId = $row['employee_id'];
                if (empty($employeeId)) {
                    $nextValue = SystemCounter::next('employee_id_sequence', Employee::count());
                    $employeeId = 'EMP-' . $year . '-' . str_pad($nextValue, 4, '0', STR_PAD_LEFT);
                }

                // 4. Create Employee
                Employee::create([
                    'user_id' => $user->id,
                    'employee_id' => $employeeId,
                    'first_name' => $row['first_name'],
                    'middle_name' => $row['middle_name'],
                    'last_name' => $row['last_name'],
                    'gender' => $row['gender'] ?? 'M',
                    'date_of_birth' => $this->parseDate($row['date_of_birth']),
                    'marital_status' => $row['marital_status'] ?? 'single',
                    'phone' => $row['phone'],
                    'email' => $row['email'],
                    'address' => $row['address'],
                    'region' => $row['region'],
                    'zone' => $row['zone'],
                    'woreda' => $row['woreda'],
                    'national_id' => $row['national_id'],
                    'tin' => $row['tin'],
                    'pension_number' => $row['pension_number'],
                    'designation' => $row['designation'] ?? 'Staff',
                    'department' => $row['department'],
                    'staff_category' => $row['staff_category'] ?? 'administrative',
                    'joining_date' => $this->parseDate($row['joining_date']) ?? now(),
                    'basic_salary' => $row['basic_salary'] ?? 0,
                    'employment_type' => $row['employment_type'] ?? 'full_time',
                    'emergency_contact_name' => $row['emergency_contact_name'],
                    'emergency_contact_phone' => $row['emergency_contact_phone'],
                    'bank_name' => $row['bank_name'],
                    'account_number' => $row['account_number'],
                    'teacher_rank' => $row['teacher_rank'],
                    'qualification_level' => $row['qualification_level'],
                    'specialization' => $row['specialization'],
                    'periods_per_week' => $row['periods_per_week'],
                    'status' => 'active',
                ]);

                DB::commit();
                $successCount++;

            } catch (\Exception $e) {
                DB::rollBack();
                $skippedCount++;
                $errors[] = "Row $rowNum: " . $e->getMessage();
            }
        }

        return [
            'success' => $successCount,
            'skipped' => $skippedCount,
            'errors' => $errors
        ];
    }

    private function parseDate(?string $date): ?string
    {
        if (empty($date)) return null;
        try {
            return Carbon::parse($date)->format('Y-m-d');
        } catch (\Exception $e) {
            throw new \Exception("Invalid date format: '$date'. Expected YYYY-MM-DD.");
        }
    }
}
