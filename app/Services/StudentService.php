<?php

namespace App\Services;

use App\Models\Student;
use App\Models\User;
use App\Models\Section;
use App\Models\AcademicYear;
use App\Models\StudentEnrollment;
use App\Models\StudentGuardian;
use App\Models\StudentMedicalInfo;
use App\Models\StudentTransportation;
use App\Models\SystemCounter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Carbon\Carbon;

class StudentService
{
    /**
     * Create a new student with all related records.
     */
    public function createStudent(array $data, ?UploadedFile $studentPhoto = null, array $guardianPhotos = [], ?UploadedFile $driverPhoto = null): Student
    {
        return DB::transaction(function () use ($data, $studentPhoto, $guardianPhotos, $driverPhoto) {
            $email = $data['email'] ?? $data['admission_number'] . '@student.renaissance.com';
            $password = Hash::make('student123');

            $user = User::create([
                'name' => "{$data['first_name']} {$data['father_name']} {$data['grandfather_name']}",
                'email' => $email,
                'password' => $password,
            ]);

            $user->assignRole('Student');

            $studentPhotoPath = null;
            if ($studentPhoto) {
                $studentPhotoPath = $studentPhoto->store('students/photos', 'public');
            }

            $nextValue = SystemCounter::next('student_id_sequence', Student::count());
            $studentId = 'STU-' . date('Y') . '-' . str_pad($nextValue, 4, '0', STR_PAD_LEFT);

            $student = Student::create([
                'user_id' => $user->id,
                'student_id' => $studentId,
                'first_name' => $data['first_name'],
                'father_name' => $data['father_name'],
                'grandfather_name' => $data['grandfather_name'],
                'middle_name' => $data['middle_name'] ?? null,
                'last_name' => $data['last_name'] ?? $data['grandfather_name'],
                'gender' => $data['gender'],
                'date_of_birth' => $data['date_of_birth'],
                'birth_country' => $data['birth_country'] ?? null,
                'birth_city' => $data['birth_city'] ?? null,
                'nationality' => $data['nationality'] ?? 'Ethiopian',
                'language_spoken' => $data['language_spoken'] ?? null,
                'admission_number' => $data['admission_number'],
                'admission_date' => $data['admission_date'],
                'photo' => $studentPhotoPath,
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
                'subcity' => $data['subcity'] ?? null,
                'woreda' => $data['woreda'] ?? null,
                'house_number' => $data['house_number'] ?? null,
                'email' => $email,
            ]);

            if (isset($data['guardians'])) {
                foreach ($data['guardians'] as $index => $guardianData) {
                    $guardianPhotoPath = null;
                    if (isset($guardianPhotos[$index])) {
                        $guardianPhotoPath = $guardianPhotos[$index]->store('guardians/photos', 'public');
                    }

                    StudentGuardian::create([
                        'student_id' => $student->id,
                        'guardian_type' => $index === 0 ? 'primary' : 'secondary',
                        'photo' => $guardianPhotoPath,
                        'first_name' => $guardianData['first_name'],
                        'father_name' => $guardianData['father_name'],
                        'grandfather_name' => $guardianData['grandfather_name'],
                        'phone' => $guardianData['phone'],
                        'email' => $guardianData['email'] ?? null,
                        'relationship' => $guardianData['relationship'],
                        'is_emergency_contact' => isset($guardianData['is_emergency_contact']) && $guardianData['is_emergency_contact'] == '1',
                        'communication_preferences' => $guardianData['communication_preferences'] ?? [],
                    ]);
                }
            }

            StudentMedicalInfo::create([
                'student_id' => $student->id,
                'blood_group' => $data['blood_group'] ?? null,
                'medical_issues' => $data['medical_issues'] ?? null,
                'current_medication' => $data['current_medication'] ?? null,
                'allergies' => $data['allergies'] ?? null,
                'emergency_contact' => $data['emergency_contact'] ?? null,
            ]);

            if ($driverPhoto || !empty($data['driver_first_name'])) {
                $driverPhotoPath = null;
                if ($driverPhoto) {
                    $driverPhotoPath = $driverPhoto->store('drivers/photos', 'public');
                }

                StudentTransportation::create([
                    'student_id' => $student->id,
                    'driver_id' => $data['driver_id'] ?? null,
                    'driver_photo' => $driverPhotoPath,
                    'driver_first_name' => $data['driver_first_name'] ?? null,
                    'driver_father_name' => $data['driver_father_name'] ?? null,
                    'driver_grandfather_name' => $data['driver_grandfather_name'] ?? null,
                    'license_number' => $data['license_number'] ?? null,
                    'vehicle_plate' => $data['vehicle_plate'] ?? null,
                    'route' => $data['route'] ?? null,
                ]);
            }

            $section = Section::findOrFail($data['section_id']);
            StudentEnrollment::create([
                'student_id' => $student->id,
                'section_id' => $section->id,
                'academic_year_id' => $section->academic_year_id,
                'enrollment_date' => $data['admission_date'],
                'status' => 'active',
            ]);

            return $student;
        });
    }

    /**
     * Update an existing student and related records.
     */
    public function updateStudent(Student $student, array $data, ?UploadedFile $studentPhoto = null, array $guardianPhotos = [], ?UploadedFile $driverPhoto = null): Student
    {
        return DB::transaction(function () use ($student, $data, $studentPhoto, $guardianPhotos, $driverPhoto) {
            if ($studentPhoto) {
                if ($student->photo) {
                    Storage::disk('public')->delete($student->photo);
                }
                $data['photo'] = $studentPhoto->store('students/photos', 'public');
            }

            $student->update($data);

            if ($student->user) {
                $student->user->update([
                    'name' => "{$student->first_name} {$student->father_name} {$student->grandfather_name}",
                ]);
            }

            if (isset($data['guardians'])) {
                foreach ($data['guardians'] as $index => $guardianData) {
                    $guardianPhotoPath = null;
                    if (isset($guardianPhotos[$index])) {
                        $guardianPhotoPath = $guardianPhotos[$index]->store('guardians/photos', 'public');
                    }

                    $updateData = [
                        'first_name' => $guardianData['first_name'],
                        'father_name' => $guardianData['father_name'],
                        'grandfather_name' => $guardianData['grandfather_name'],
                        'phone' => $guardianData['phone'],
                        'email' => $guardianData['email'] ?? null,
                        'relationship' => $guardianData['relationship'],
                        'is_emergency_contact' => isset($guardianData['is_emergency_contact']) && $guardianData['is_emergency_contact'] == '1',
                        'communication_preferences' => $guardianData['communication_preferences'] ?? [],
                    ];

                    if ($guardianPhotoPath) {
                        $updateData['photo'] = $guardianPhotoPath;
                    }

                    if (isset($guardianData['id']) && $guardianData['id']) {
                        $guardian = StudentGuardian::find($guardianData['id']);
                        if ($guardian) {
                            if ($guardianPhotoPath && $guardian->photo) {
                                Storage::disk('public')->delete($guardian->photo);
                            }
                            $guardian->update($updateData);
                        }
                    } else {
                        $updateData['student_id'] = $student->id;
                        $updateData['guardian_type'] = $index === 0 ? 'primary' : 'secondary';
                        StudentGuardian::create($updateData);
                    }
                }
            }

            if (isset($data['blood_type']) || isset($data['allergies']) || isset($data['medical_conditions'])) {
                $student->medicalInfo()->updateOrCreate(
                    ['student_id' => $student->id],
                    [
                        'blood_group' => $data['blood_type'] ?? $student->medicalInfo->blood_group ?? null,
                        'allergies' => $data['allergies'] ?? $student->medicalInfo->allergies ?? null,
                        'medical_issues' => $data['medical_conditions'] ?? $student->medicalInfo->medical_issues ?? null,
                        'current_medication' => $data['current_medication'] ?? $student->medicalInfo->current_medication ?? null,
                        'emergency_contact' => ($data['emergency_contact_name'] ?? '') . (isset($data['emergency_contact_phone']) ? ' - ' . $data['emergency_contact_phone'] : ''),
                    ]
                );
            }

            if (isset($data['uses_school_transport']) && $data['uses_school_transport']) {
                $transportation = $student->transportation;
                $driverPhotoPath = $transportation->driver_photo ?? null;

                if ($driverPhoto) {
                    if ($transportation && $transportation->driver_photo) {
                        Storage::disk('public')->delete($transportation->driver_photo);
                    }
                    $driverPhotoPath = $driverPhoto->store('drivers', 'public');
                }

                $driverNameParts = explode(' ', $data['driver_name'] ?? '');
                
                $student->transportation()->updateOrCreate(
                    ['student_id' => $student->id],
                    [
                        'driver_first_name' => $driverNameParts[0] ?? null,
                        'driver_father_name' => $driverNameParts[1] ?? null,
                        'driver_grandfather_name' => $driverNameParts[2] ?? null,
                        'route' => $data['transport_route'] ?? null,
                        'driver_photo' => $driverPhotoPath,
                    ]
                );
            }

            return $student;
        });
    }

    /**
     * Delete a student and all related resources.
     */
    public function deleteStudent(Student $student): void
    {
        DB::transaction(function () use ($student) {
            // Delete Photos
            if ($student->photo) {
                Storage::disk('public')->delete($student->photo);
            }

            foreach ($student->guardians as $guardian) {
                if ($guardian->photo) {
                    Storage::disk('public')->delete($guardian->photo);
                }
            }

            if ($student->transportation && $student->transportation->driver_photo) {
                Storage::disk('public')->delete($student->transportation->driver_photo);
            }

            // Delete User Account
            if ($student->user) {
                $student->user->delete();
            }

            $student->delete();
        });
    }

    /**
     * Delete multiple students.
     */
    public function bulkDeleteStudents(array $ids): int
    {
        $count = 0;
        foreach ($ids as $id) {
            $student = Student::find($id);
            if ($student) {
                $this->deleteStudent($student);
                $count++;
            }
        }
        return $count;
    }

    /**
     * Bulk import students from structured data.
     */
    public function bulkImport(array $rows, AcademicYear $activeYear): array
    {
        $successCount = 0;
        $skippedCount = 0;
        $errors = [];
        
        $defaultPassword = Hash::make('student123');
        $year = date('Y');
        
        $nextIdSeq = $this->getNextStudentIdSequence($year);

        foreach ($rows as $index => $row) {
            $rowNum = $index + 2; // +1 for header, +1 for 0-indexing

            try {
                DB::beginTransaction();

                // 1. Validate Section
                $section = $this->findSection($row['grade_level'], $row['section_name'], $activeYear->id);
                if (!$section) {
                    throw new \Exception("Section '{$row['section_name']}' (Grade {$row['grade_level']}) not found in active year.");
                }

                // 2. Check Existence
                $existingStudent = Student::where('admission_number', $row['admission_number'])->first();
                if ($existingStudent) {
                    $this->enrollExistingStudent($existingStudent, $section, $activeYear, $row['admission_date']);
                    DB::commit();
                    $successCount++;
                    continue;
                }

                // 3. New Student
                $email = $row['email'] ?: $row['admission_number'] . '@student.renaissance.com';
                if (User::where('email', $email)->exists()) {
                    throw new \Exception("User/Email '$email' already exists.");
                }

                $user = User::create([
                    'name' => "{$row['first_name']} {$row['father_name']} {$row['grandfather_name']}",
                    'email' => $email,
                    'password' => $defaultPassword,
                ]);
                $user->assignRole('Student');

                $studentId = 'STU-' . $year . '-' . str_pad($nextIdSeq++, 4, '0', STR_PAD_LEFT);

                $student = Student::create([
                    'user_id' => $user->id,
                    'student_id' => $row['student_id'] ?: $studentId,
                    'first_name' => $row['first_name'],
                    'father_name' => $row['father_name'],
                    'grandfather_name' => $row['grandfather_name'],
                    'last_name' => $row['grandfather_name'],
                    'gender' => $row['gender'],
                    'date_of_birth' => $this->parseDate($row['date_of_birth']),
                    'birth_country' => $row['birth_country'],
                    'birth_city' => $row['birth_city'],
                    'nationality' => $row['nationality'] ?? 'Ethiopian',
                    'language_spoken' => $row['language_spoken'],
                    'admission_number' => $row['admission_number'],
                    'admission_date' => $this->parseDate($row['admission_date']),
                    'phone' => $row['phone'],
                    'subcity' => $row['subcity'],
                    'woreda' => $row['woreda'],
                    'house_number' => $row['house_number'],
                    'is_active' => true,
                ]);

                $student->enrollments()->create([
                    'section_id' => $section->id,
                    'academic_year_id' => $activeYear->id,
                    'status' => 'active',
                    'enrollment_date' => $this->parseDate($row['admission_date']),
                ]);

                if (!empty($row['primary_guardian_first_name'])) {
                    $student->guardians()->create([
                        'first_name' => $row['primary_guardian_first_name'],
                        'father_name' => $row['primary_guardian_father_name'],
                        'grandfather_name' => $row['primary_guardian_grandfather_name'],
                        'phone' => $row['primary_guardian_phone'],
                        'email' => $row['primary_guardian_email'],
                        'relationship' => $row['primary_guardian_relationship'],
                        'guardian_type' => 'primary',
                    ]);
                }

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

    private function getNextStudentIdSequence(string $year): int
    {
        $lastStudent = Student::where('student_id', 'like', "STU-{$year}-%")
            ->orderBy('student_id', 'desc')
            ->first();
        
        if (!$lastStudent) return 1;
        
        $parts = explode('-', $lastStudent->student_id);
        return (int)end($parts) + 1;
    }

    private function findSection(string $gradeName, string $sectionName, int $yearId): ?Section
    {
        return Section::where('name', $sectionName)
            ->whereHas('gradeLevel', fn($q) => $q->where('name', $gradeName))
            ->where('academic_year_id', $yearId)
            ->first();
    }

    private function enrollExistingStudent(Student $student, Section $section, AcademicYear $year, ?string $date): void
    {
        $exists = $student->enrollments()
            ->where('section_id', $section->id)
            ->where('academic_year_id', $year->id)
            ->exists();

        if ($exists) {
            throw new \Exception("Student already enrolled in this section for this academic year.");
        }

        $student->enrollments()->create([
            'section_id' => $section->id,
            'academic_year_id' => $year->id,
            'status' => 'active',
            'enrollment_date' => $this->parseDate($date) ?? now(),
        ]);
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
