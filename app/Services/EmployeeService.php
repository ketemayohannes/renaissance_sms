<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\User;
use App\Models\SystemCounter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Models\AcademicStaffDetail;
use App\Models\AdministrativeStaffDetail;
use Carbon\Carbon;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Validator;

class EmployeeService
{
    /**
     * Centralized method to create an employee and their user account.
     */
    public function createEmployee(array $data): Employee
    {
        return DB::transaction(function () use ($data) {
            // 1. Auto-generate email if missing
            $email = (!empty($data['email'])) ? $data['email'] : $this->generateEmail($data['first_name'], $data['last_name']);

            // 2. Create User
            $firstName = $data['first_name'];
            $middleName = $data['middle_name'] ?? '';
            $lastName = $data['last_name'];
            $fullName = trim("{$firstName} {$middleName} {$lastName}");

            $user = User::create([
                'name' => $fullName,
                'email' => $email,
                'password' => Hash::make($data['password'] ?? 'staff1234'),
            ]);

            // 3. Assign Role
            $roleName = $data['role'] ?? $this->determineRole($data);
            $user->assignRole($roleName);

            // 4. Generate Employee ID if not provided
            $employeeId = $data['employee_id'] ?? $this->generateEmployeeId();

            // 5. Create Employee
            $employee = Employee::create(array_merge($data, [
                'user_id' => $user->id,
                'employee_id' => $employeeId,
                'email' => $email,
                'gender' => $data['gender'] ?? 'M',
                'designation' => $data['designation'] ?? $roleName, // Use roleName for backward compatibility
                'basic_salary' => $data['basic_salary'] ?? 0,
                'joining_date' => $this->parseDate($data['joining_date'] ?? null) ?? now(),
                'date_of_birth' => $this->parseDate($data['date_of_birth'] ?? null),
                'status' => $data['status'] ?? 'active',
            ]));

            // 6. Handle Category-Specific Details
            $role = Role::where('name', $roleName)->first();
            $category = $role->category ?? $data['staff_category'] ?? null;

            if ($category === 'academic') {
                AcademicStaffDetail::create([
                    'employee_id' => $employee->id,
                    'teacher_rank' => $data['teacher_rank'] ?? null,
                    'qualification_level' => $data['qualification_level'] ?? null,
                    'specialization' => $data['specialization'] ?? null,
                    'periods_per_week' => $data['periods_per_week'] ?? null,
                    'secondary_responsibilities' => $data['secondary_responsibilities'] ?? null,
                    'institution' => $data['institution'] ?? null,
                    'graduation_year' => $data['graduation_year'] ?? null,
                    'last_degree' => $data['last_degree'] ?? null,
                ]);
            } elseif ($category === 'administrative') {
                AdministrativeStaffDetail::create([
                    'employee_id' => $employee->id,
                    'system_access_roles' => $data['system_access_roles'] ?? null,
                    'qualification_level' => $data['qualification_level'] ?? null,
                    'specialization' => $data['specialization'] ?? null,
                    'institution' => $data['institution'] ?? null,
                    'graduation_year' => $data['graduation_year'] ?? null,
                    'last_degree' => $data['last_degree'] ?? null,
                ]);
            }

            // 7. Handle Documents
            if (isset($data['documents']) && is_array($data['documents'])) {
                $this->uploadDocuments($employee, $data['documents']);
            }

            return $employee;
        });
    }

    /**
     * Centralized method to update an employee.
     */
    public function updateEmployee(Employee $employee, array $data): bool
    {
        return DB::transaction(function () use ($employee, $data) {
            // Update User record
            if (isset($data['first_name']) || isset($data['last_name']) || isset($data['email'])) {
                $userUpdate = [];
                if (isset($data['first_name']) || isset($data['last_name'])) {
                    $userUpdate['name'] = trim(($data['first_name'] ?? $employee->first_name) . " " . ($data['middle_name'] ?? $employee->middle_name) . " " . ($data['last_name'] ?? $employee->last_name));
                }
                if (isset($data['email'])) {
                    $userUpdate['email'] = $data['email'];
                }
                $employee->user->update($userUpdate);
            }

            // Handle role update
            if (isset($data['role']) && $data['role'] !== $employee->user->roles->first()->name) {
                $employee->user->syncRoles([$data['role']]);
            }

            // Update Details
            $role = $employee->user->roles->first();
            if ($role && $role->category === 'academic') {
                $academicFields = [
                    'teacher_rank', 'qualification_level', 'specialization', 'periods_per_week', 
                    'secondary_responsibilities', 'institution', 'graduation_year', 'last_degree'
                ];
                $academicData = [];
                foreach ($academicFields as $field) {
                    if (array_key_exists($field, $data)) {
                        $academicData[$field] = $data[$field];
                    }
                }
                if (!empty($academicData)) {
                    $employee->academicDetails()->updateOrCreate(['employee_id' => $employee->id], $academicData);
                }
            } elseif ($role && $role->category === 'administrative') {
                $adminFields = [
                    'system_access_roles', 'qualification_level', 'specialization', 
                    'institution', 'graduation_year', 'last_degree'
                ];
                $adminData = [];
                foreach ($adminFields as $field) {
                    if (array_key_exists($field, $data)) {
                        $adminData[$field] = $data[$field];
                    }
                }
                if (!empty($adminData)) {
                    $employee->administrativeDetails()->updateOrCreate(['employee_id' => $employee->id], $adminData);
                }
            }

            // Handle Documents
            if (isset($data['documents']) && is_array($data['documents'])) {
                $this->uploadDocuments($employee, $data['documents']);
            }

            // Parse dates
            if (isset($data['joining_date'])) $data['joining_date'] = $this->parseDate($data['joining_date']);
            if (isset($data['date_of_birth'])) $data['date_of_birth'] = $this->parseDate($data['date_of_birth']);
            if (isset($data['leaving_date'])) $data['leaving_date'] = $this->parseDate($data['leaving_date']);

            return $employee->update($data);
        });
    }

    /**
     * Optimized bulk import with batching and centralized logic.
     */
    public function bulkImport(array $rows): array
    {
        $successCount = 0;
        $skippedCount = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            $rowNum = $index + 2;

            try {
                // Validation
                $validator = Validator::make($row, [
                    'first_name' => 'required|string|max:255',
                    'last_name' => 'required|string|max:255',
                    'email' => 'nullable|email|unique:users,email|unique:employees,email',
                ]);

                if ($validator->fails()) {
                    throw new \Exception(implode(' ', $validator->errors()->all()));
                }

                // Check for Employee ID uniqueness if provided
                if (!empty($row['employee_id']) && Employee::where('employee_id', $row['employee_id'])->exists()) {
                    throw new \Exception("Employee ID '{$row['employee_id']}' already exists.");
                }

                $this->createEmployee($row);
                $successCount++;

            } catch (\Exception $e) {
                $skippedCount++;
                $errors[] = "Row $rowNum: " . $e->getMessage();
                Log::error("Import error at row $rowNum: " . $e->getMessage());
            }
        }

        return [
            'success' => $successCount,
            'skipped' => $skippedCount,
            'errors' => $errors
        ];
    }

    /**
     * Determine role based on designation or category.
     */
    private function determineRole(array $data): string
    {
        if (!empty($data['designation'])) {
            return $data['designation']; // Use directly if provided as specific role name
        }

        return ($data['staff_category'] ?? '') === 'academic' ? 'Teacher' : 'Secretary';
    }

    public function getRolesByCategory(): array
    {
        return [
            'academic' => Role::where('category', 'academic')->orderBy('name')->pluck('name'),
            'administrative' => Role::where('category', 'administrative')->orderBy('name')->pluck('name'),
        ];
    }

    /**
     * Generate a unique Employee ID.
     */
    private function generateEmployeeId(): string
    {
        $year = date('Y');
        $nextValue = SystemCounter::next('employee_id_sequence', Employee::count());
        return 'EMP-' . $year . '-' . str_pad($nextValue, 4, '0', STR_PAD_LEFT);
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

    public function uploadDocuments(Employee $employee, array $files): void
    {
        foreach ($files as $type => $file) {
            if (!$file instanceof \Illuminate\Http\UploadedFile) continue;

            // Delete old document of same type if exists
            $existing = $employee->documents()->where('type', $type)->first();
            if ($existing) {
                Storage::disk('public')->delete($existing->file_path);
                $existing->delete();
            }

            $path = $file->store("employees/documents/{$employee->employee_id}", 'public');
            
            $employee->documents()->create([
                'type' => $type,
                'name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'file_extension' => $file->getClientOriginalExtension(),
                'file_size' => $file->getSize(),
            ]);
        }
    }

    private function generateEmail(string $firstName, string $lastName): string
    {
        $base = strtolower(Str::slug($firstName) . '.' . Str::slug($lastName));
        $domain = '@renaissance.edu.et';
        $email = $base . $domain;
        
        $counter = 1;
        while (User::where('email', $email)->exists() || Employee::where('email', $email)->exists()) {
            $counter++;
            $email = $base . $counter . $domain;
        }
        
        return $email;
    }
}
