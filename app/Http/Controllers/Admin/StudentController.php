<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use App\Models\Section;
use App\Models\AcademicYear;
use App\Models\StudentEnrollment;
use App\Models\StudentGuardian;
use App\Models\StudentMedicalInfo;
use App\Models\StudentTransportation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $query = Student::with(['user', 'enrollments.section.gradeLevel', 'enrollments.academicYear']);

        // Search Filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('father_name', 'like', "%{$search}%")
                  ->orWhere('grandfather_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('student_id', 'like', "%{$search}%")
                  ->orWhere('admission_number', 'like', "%{$search}%");
            });
        }

        // Gender Filter
        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }

        // Status Filter
        if ($request->filled('status')) {
            if ($request->status == 'blocked') {
                $query->where('is_active', false);
            } elseif ($request->status == 'active') {
                $query->where('is_active', true);
            }
        }

        // Grade & Section Filter
        if ($request->filled('grade_id') || $request->filled('section_id')) {
            $query->whereHas('enrollments', function($q) use ($request) {
                // We typically want to filter based on CURRENT enrollment
                $q->whereNull('end_date'); 
                
                if ($request->filled('section_id')) {
                    $q->where('section_id', $request->section_id);
                }
                
                if ($request->filled('grade_id')) {
                    $q->whereHas('section', function($sq) use ($request) {
                        $sq->where('grade_level_id', $request->grade_id);
                    });
                }
            });
        }

        $students = $query->latest()->paginate(15)->withQueryString();
        
        // Data for filters
        $gradeLevels = \App\Models\GradeLevel::all();
        $sections = Section::with('gradeLevel')->get()->groupBy(function($section) {
            return $section->gradeLevel ? $section->gradeLevel->name : 'Unassigned';
        });

        return view('admin.students.index', compact('students', 'gradeLevels', 'sections'));
    }

    public function create()
    {
        $activeYear = AcademicYear::where('is_active', true)->first();
        $sections = Section::with('gradeLevel.division')
            ->where('is_active', true)
            ->when($activeYear, function($q) use ($activeYear) {
                return $q->where('academic_year_id', $activeYear->id);
            })
            ->get();
            
        return view('admin.students.create', compact('sections', 'activeYear'));
    }

    public function store(Request $request)
    {
        $request->validate([
            // Personal Info
            'first_name' => 'required|string|max:255',
            'father_name' => 'required|string|max:255',
            'grandfather_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'gender' => 'required|in:M,F',
            'date_of_birth' => 'required|date',
            'birth_country' => 'nullable|string|max:255',
            'birth_city' => 'nullable|string|max:255',
            'nationality' => 'nullable|string|max:255',
            'language_spoken' => 'nullable|string|max:255',
            'student_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            
            // Address
            'subcity' => 'nullable|string|in:Addis Ketema,Akaki Kality,Arada,Bole,Gullele,Kirkos,Kolfe Keranio,Lideta,Nifas Silk-Lafto,Yeka,Lemi Kura',
            'woreda' => 'nullable|string|max:255',
            'house_number' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            
            // Admission
            'admission_number' => 'required|string|unique:students,admission_number',
            'admission_date' => 'required|date',
            'section_id' => 'required|exists:sections,id',
            'email' => 'nullable|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            
            // Primary Guardian
            'primary_guardian_first_name' => 'required|string|max:255',
            'primary_guardian_father_name' => 'required|string|max:255',
            'primary_guardian_grandfather_name' => 'required|string|max:255',
            'primary_guardian_phone' => 'required|string|max:20',
            'primary_guardian_email' => 'nullable|email',
            'primary_guardian_relationship' => 'required|string|in:Father,Mother,Guardian',
            'primary_guardian_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            
            // Secondary Guardian (Optional)
            'secondary_guardian_first_name' => 'nullable|string|max:255',
            'secondary_guardian_father_name' => 'nullable|string|max:255',
            'secondary_guardian_grandfather_name' => 'nullable|string|max:255',
            'secondary_guardian_phone' => 'nullable|string|max:20',
            'secondary_guardian_email' => 'nullable|email',
            'secondary_guardian_relationship' => 'nullable|string|in:Father,Mother,Guardian',
            'secondary_guardian_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            
            // Medical Info
            'blood_group' => 'nullable|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
            'medical_issues' => 'nullable|string',
            'current_medication' => 'nullable|string',
            'allergies' => 'nullable|string',
            'emergency_contact' => 'nullable|string|max:20',
            
            // Transportation (Optional)
            'driver_id' => 'nullable|string|max:255',
            'driver_first_name' => 'nullable|string|max:255',
            'driver_father_name' => 'nullable|string|max:255',
            'driver_grandfather_name' => 'nullable|string|max:255',
            'license_number' => 'nullable|string|max:255',
            'vehicle_plate' => 'nullable|string|max:255',
            'route' => 'nullable|string|max:255',
            'driver_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        try {
            DB::beginTransaction();

            // 1. Create User Account
            $email = $request->email ?? $request->admission_number . '@renaissance.edu.et';
            $password = Hash::make('student123'); // Default password

            $user = User::create([
                'name' => $request->first_name . ' ' . $request->father_name . ' ' . $request->grandfather_name,
                'email' => $email,
                'password' => $password,
            ]);

            $user->assignRole('Student');

            // 2. Handle Student Photo Upload
            $studentPhotoPath = null;
            if ($request->hasFile('student_photo')) {
                $studentPhotoPath = $request->file('student_photo')->store('students/photos', 'public');
            }

            // 3. Create Student Record
            $studentId = 'STU-' . date('Y') . '-' . str_pad(Student::count() + 1, 4, '0', STR_PAD_LEFT);

            $student = Student::create([
                'user_id' => $user->id,
                'student_id' => $studentId,
                'first_name' => $request->first_name,
                'father_name' => $request->father_name,
                'grandfather_name' => $request->grandfather_name,
                'middle_name' => $request->middle_name,
                'last_name' => $request->last_name ?? $request->grandfather_name,
                'gender' => $request->gender,
                'date_of_birth' => $request->date_of_birth,
                'birth_country' => $request->birth_country,
                'birth_city' => $request->birth_city,
                'nationality' => $request->nationality ?? 'Ethiopian',
                'language_spoken' => $request->language_spoken,
                'admission_number' => $request->admission_number,
                'admission_date' => $request->admission_date,
                'photo' => $studentPhotoPath,
                'phone' => $request->phone,
                'address' => $request->address,
                'subcity' => $request->subcity,
                'woreda' => $request->woreda,
                'house_number' => $request->house_number,
                'email' => $email,
            ]);

            // 4. Create Primary Guardian
            $primaryGuardianPhotoPath = null;
            if ($request->hasFile('primary_guardian_photo')) {
                $primaryGuardianPhotoPath = $request->file('primary_guardian_photo')->store('guardians/photos', 'public');
            }

            StudentGuardian::create([
                'student_id' => $student->id,
                'guardian_type' => 'primary',
                'photo' => $primaryGuardianPhotoPath,
                'first_name' => $request->primary_guardian_first_name,
                'father_name' => $request->primary_guardian_father_name,
                'grandfather_name' => $request->primary_guardian_grandfather_name,
                'phone' => $request->primary_guardian_phone,
                'email' => $request->primary_guardian_email,
                'relationship' => $request->primary_guardian_relationship,
            ]);

            // 5. Create Secondary Guardian (if provided)
            if ($request->filled('secondary_guardian_first_name')) {
                $secondaryGuardianPhotoPath = null;
                if ($request->hasFile('secondary_guardian_photo')) {
                    $secondaryGuardianPhotoPath = $request->file('secondary_guardian_photo')->store('guardians/photos', 'public');
                }

                StudentGuardian::create([
                    'student_id' => $student->id,
                    'guardian_type' => 'secondary',
                    'photo' => $secondaryGuardianPhotoPath,
                    'first_name' => $request->secondary_guardian_first_name,
                    'father_name' => $request->secondary_guardian_father_name,
                    'grandfather_name' => $request->secondary_guardian_grandfather_name,
                    'phone' => $request->secondary_guardian_phone,
                    'email' => $request->secondary_guardian_email,
                    'relationship' => $request->secondary_guardian_relationship,
                ]);
            }

            // 6. Create Medical Info
            StudentMedicalInfo::create([
                'student_id' => $student->id,
                'blood_group' => $request->blood_group,
                'medical_issues' => $request->medical_issues,
                'current_medication' => $request->current_medication,
                'allergies' => $request->allergies,
                'emergency_contact' => $request->emergency_contact,
            ]);

            // 7. Create Transportation Info (if provided)
            if ($request->filled('driver_first_name')) {
                $driverPhotoPath = null;
                if ($request->hasFile('driver_photo')) {
                    $driverPhotoPath = $request->file('driver_photo')->store('drivers/photos', 'public');
                }

                StudentTransportation::create([
                    'student_id' => $student->id,
                    'driver_id' => $request->driver_id,
                    'driver_photo' => $driverPhotoPath,
                    'driver_first_name' => $request->driver_first_name,
                    'driver_father_name' => $request->driver_father_name,
                    'driver_grandfather_name' => $request->driver_grandfather_name,
                    'license_number' => $request->license_number,
                    'vehicle_plate' => $request->vehicle_plate,
                    'route' => $request->route,
                ]);
            }

            // 8. Create Enrollment
            $section = Section::find($request->section_id);
            
            StudentEnrollment::create([
                'student_id' => $student->id,
                'section_id' => $section->id,
                'academic_year_id' => $section->academic_year_id,
                'enrollment_date' => $request->admission_date,
                'status' => 'active',
            ]);

            DB::commit();

            return redirect()->route('admin.students.index')
                ->with('success', 'Student registered successfully. Student ID: ' . $studentId);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error registering student: ' . $e->getMessage())->withInput();
        }
    }

    public function show(Student $student)
    {
        $student->load([
            'user', 
            'guardians',
            'medicalInfo',
            'transportation',
            'siblings.enrollments.section.gradeLevel' // Load siblings with their current section info
        ]);
        
        // Load all enrollments (including historical) ordered by date
        $enrollments = $student->enrollments()
            ->with(['section.gradeLevel', 'academicYear'])
            ->orderBy('enrollment_date', 'desc')
            ->get();
        
        return view('admin.students.show', compact('student', 'enrollments'));
    }

    public function linkSibling(Request $request, Student $student)
    {
        $request->validate([
            'sibling_id' => 'required|exists:students,id|different:student_id',
        ]);

        $sibling = Student::findOrFail($request->sibling_id);
        $student->addSibling($sibling);

        return back()->with('success', 'Sibling linked successfully.');
    }

    public function unlinkSibling(Student $student, Student $sibling)
    {
        $student->removeSibling($sibling);
        return back()->with('success', 'Sibling unlinked successfully.');
    }

    public function edit(Student $student)
    {
        $student->load(['guardians', 'medicalInfo', 'transportation']);
        
        $activeYear = AcademicYear::where('is_active', true)->first();
        $sections = Section::with('gradeLevel.division')
            ->where('is_active', true)
            ->when($activeYear, function($q) use ($activeYear) {
                return $q->where('academic_year_id', $activeYear->id);
            })
            ->get();
            
        return view('admin.students.edit', compact('student', 'sections', 'activeYear'));
    }

    public function update(Request $request, Student $student)
    {
        $request->validate([
            // Personal Info
            'first_name' => 'required|string|max:255',
            'father_name' => 'required|string|max:255',
            'grandfather_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'gender' => 'required|in:M,F',
            'date_of_birth' => 'required|date',
            'birth_country' => 'nullable|string|max:255',
            'birth_city' => 'nullable|string|max:255',
            'nationality' => 'nullable|string|max:255',
            'language_spoken' => 'nullable|string|max:255',
            
            // Admission Info
            'admission_number' => 'required|string|unique:students,admission_number,' . $student->id,
            'admission_date' => 'required|date',
            'photo' => 'nullable|image|max:2048',
            
            // Address
            'subcity' => 'nullable|in:Addis Ketema,Akaki Kality,Arada,Bole,Gullele,Kirkos,Kolfe Keranio,Lideta,Nifas Silk-Lafto,Yeka,Lemi Kura',
            'woreda' => 'nullable|string|max:50',
            'house_number' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:20',
            
            // Primary Guardian
            'primary_guardian_first_name' => 'nullable|string|max:255',
            'primary_guardian_father_name' => 'nullable|string|max:255',
            'primary_guardian_grandfather_name' => 'nullable|string|max:255',
            'primary_guardian_phone' => 'nullable|string|max:20',
            'primary_guardian_email' => 'nullable|email|max:255',
            'primary_guardian_relationship' => 'nullable|in:Father,Mother,Guardian',
            'primary_guardian_photo' => 'nullable|image|max:2048',
            
            // Secondary Guardian
            'secondary_guardian_first_name' => 'nullable|string|max:255',
            'secondary_guardian_father_name' => 'nullable|string|max:255',
            'secondary_guardian_grandfather_name' => 'nullable|string|max:255',
            'secondary_guardian_phone' => 'nullable|string|max:20',
            'secondary_guardian_email' => 'nullable|email|max:255',
            'secondary_guardian_relationship' => 'nullable|in:Father,Mother,Guardian',
            'secondary_guardian_photo' => 'nullable|image|max:2048',
            
            // Medical
            'blood_type' => 'nullable|string|max:10',
            'allergies' => 'nullable|string',
            'medical_conditions' => 'nullable|string',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            
            // Transportation
            'uses_school_transport' => 'nullable|boolean',
            'transport_route' => 'nullable|string|max:255',
            'pickup_location' => 'nullable|string|max:255',
            'driver_name' => 'nullable|string|max:255',
            'driver_phone' => 'nullable|string|max:20',
            'driver_photo' => 'nullable|image|max:2048',
        ]);

        DB::beginTransaction();
        try {
            // Handle student photo
            $photoPath = $student->photo;
            if ($request->hasFile('photo')) {
                if ($student->photo) {
                    Storage::disk('public')->delete($student->photo);
                }
                $photoPath = $request->file('photo')->store('students', 'public');
            }

            // Update student
            $student->update([
                'first_name' => $request->first_name,
                'father_name' => $request->father_name,
                'grandfather_name' => $request->grandfather_name,
                'middle_name' => $request->middle_name,
                'last_name' => $request->last_name ?? $request->grandfather_name,
                'gender' => $request->gender,
                'date_of_birth' => $request->date_of_birth,
                'birth_country' => $request->birth_country,
                'birth_city' => $request->birth_city,
                'nationality' => $request->nationality,
                'language_spoken' => $request->language_spoken,
                'admission_number' => $request->admission_number,
                'admission_date' => $request->admission_date,
                'photo' => $photoPath,
                'subcity' => $request->subcity,
                'woreda' => $request->woreda,
                'house_number' => $request->house_number,
                'phone' => $request->phone,
            ]);

            // Update user name
            $student->user->update([
                'name' => $request->first_name . ' ' . $request->father_name . ' ' . $request->grandfather_name
            ]);

            // Update Primary Guardian
            if ($request->filled('primary_guardian_first_name')) {
                $primaryGuardian = $student->guardians()->where('guardian_type', 'primary')->first();
                
                $primaryPhotoPath = $primaryGuardian->photo ?? null;
                if ($request->hasFile('primary_guardian_photo')) {
                    if ($primaryGuardian && $primaryGuardian->photo) {
                        Storage::disk('public')->delete($primaryGuardian->photo);
                    }
                    $primaryPhotoPath = $request->file('primary_guardian_photo')->store('guardians', 'public');
                }

                if ($primaryGuardian) {
                    $primaryGuardian->update([
                        'first_name' => $request->primary_guardian_first_name,
                        'father_name' => $request->primary_guardian_father_name,
                        'grandfather_name' => $request->primary_guardian_grandfather_name,
                        'phone' => $request->primary_guardian_phone,
                        'email' => $request->primary_guardian_email,
                        'relationship' => $request->primary_guardian_relationship,
                        'photo' => $primaryPhotoPath,
                    ]);
                } else {
                    $student->guardians()->create([
                        'first_name' => $request->primary_guardian_first_name,
                        'father_name' => $request->primary_guardian_father_name,
                        'grandfather_name' => $request->primary_guardian_grandfather_name,
                        'phone' => $request->primary_guardian_phone,
                        'email' => $request->primary_guardian_email,
                        'relationship' => $request->primary_guardian_relationship,
                        'photo' => $primaryPhotoPath,
                        'guardian_type' => 'primary',
                    ]);
                }
            }

            // Update Secondary Guardian
            if ($request->filled('secondary_guardian_first_name')) {
                $secondaryGuardian = $student->guardians()->where('guardian_type', 'secondary')->first();
                
                $secondaryPhotoPath = $secondaryGuardian->photo ?? null;
                if ($request->hasFile('secondary_guardian_photo')) {
                    if ($secondaryGuardian && $secondaryGuardian->photo) {
                        Storage::disk('public')->delete($secondaryGuardian->photo);
                    }
                    $secondaryPhotoPath = $request->file('secondary_guardian_photo')->store('guardians', 'public');
                }

                if ($secondaryGuardian) {
                    $secondaryGuardian->update([
                        'first_name' => $request->secondary_guardian_first_name,
                        'father_name' => $request->secondary_guardian_father_name,
                        'grandfather_name' => $request->secondary_guardian_grandfather_name,
                        'phone' => $request->secondary_guardian_phone,
                        'email' => $request->secondary_guardian_email,
                        'relationship' => $request->secondary_guardian_relationship,
                        'photo' => $secondaryPhotoPath,
                    ]);
                } else {
                    $student->guardians()->create([
                        'first_name' => $request->secondary_guardian_first_name,
                        'father_name' => $request->secondary_guardian_father_name,
                        'grandfather_name' => $request->secondary_guardian_grandfather_name,
                        'phone' => $request->secondary_guardian_phone,
                        'email' => $request->secondary_guardian_email,
                        'relationship' => $request->secondary_guardian_relationship,
                        'photo' => $secondaryPhotoPath,
                        'guardian_type' => 'secondary',
                    ]);
                }
            }

            // Update Medical Info
            if ($request->filled(['blood_type', 'allergies', 'medical_conditions'])) {
                $student->medicalInfo()->updateOrCreate(
                    ['student_id' => $student->id],
                    [
                        'blood_group' => $request->blood_type,
                        'allergies' => $request->allergies,
                        'medical_issues' => $request->medical_conditions,
                        'current_medication' => $request->current_medication,
                        'emergency_contact' => $request->emergency_contact_name . ($request->emergency_contact_phone ? ' - ' . $request->emergency_contact_phone : ''),
                    ]
                );
            }

            // Update Transportation
            if ($request->filled('uses_school_transport')) {
                $transportation = $student->transportation;
                
                $driverPhotoPath = $transportation->driver_photo ?? null;
                if ($request->hasFile('driver_photo')) {
                    if ($transportation && $transportation->driver_photo) {
                        Storage::disk('public')->delete($transportation->driver_photo);
                    }
                    $driverPhotoPath = $request->file('driver_photo')->store('drivers', 'public');
                }

                // Parse driver name into Ethiopian name parts
                $driverNameParts = explode(' ', $request->driver_name ?? '');
                $driverFirstName = $driverNameParts[0] ?? null;
                $driverFatherName = $driverNameParts[1] ?? null;
                $driverGrandfatherName = $driverNameParts[2] ?? null;

                $student->transportation()->updateOrCreate(
                    ['student_id' => $student->id],
                    [
                        'driver_first_name' => $driverFirstName,
                        'driver_father_name' => $driverFatherName,
                        'driver_grandfather_name' => $driverGrandfatherName,
                        'route' => $request->transport_route,
                        'driver_photo' => $driverPhotoPath,
                    ]
                );
            }

            DB::commit();
            return redirect()->route('admin.students.show', $student)
                ->with('success', 'Student updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Update failed: ' . $e->getMessage());
        }
    }

    public function export()
    {
        $students = Student::with(['guardians', 'medicalInfo', 'transportation', 'enrollments.section.gradeLevel'])
            ->orderBy('created_at', 'desc')
            ->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="students_export_' . date('Y-m-d') . '.csv"',
        ];

        $columns = [
            'Student ID', 'First Name', 'Father Name', 'Grandfather Name', 'Gender', 'Date of Birth',
            'Birth Country', 'Birth City', 'Nationality', 'Language Spoken',
            'Admission Number', 'Admission Date', 'Current Grade', 'Current Section',
            'Subcity', 'Woreda', 'House Number', 'Phone', 'Email',
            'Primary Guardian Name', 'Primary Guardian Phone', 'Primary Guardian Email', 'Primary Guardian Relationship',
            'Secondary Guardian Name', 'Secondary Guardian Phone', 'Secondary Guardian Email', 'Secondary Guardian Relationship',
            'Blood Type', 'Allergies', 'Medical Issues',
            'Driver Name', 'Route', 'Status'
        ];

        $callback = function() use ($students, $columns) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for Excel compatibility
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Force Excel to use comma as separator
            fwrite($file, "sep=,\n");
            
            fputcsv($file, $columns);
            
            foreach ($students as $student) {
                $primaryGuardian = $student->guardians->where('guardian_type', 'primary')->first();
                $secondaryGuardian = $student->guardians->where('guardian_type', 'secondary')->first();
                $enrollment = $student->enrollments->first();
                
                $row = [
                    $student->student_id,
                    $student->first_name,
                    $student->father_name,
                    $student->grandfather_name,
                    $student->gender,
                    $student->date_of_birth,
                    $student->birth_country,
                    $student->birth_city,
                    $student->nationality,
                    $student->language_spoken,
                    $student->admission_number,
                    $student->admission_date,
                    $enrollment ? $enrollment->section->gradeLevel->name : '',
                    $enrollment ? $enrollment->section->name : '',
                    $student->subcity,
                    $student->woreda,
                    $student->house_number,
                    $student->phone,
                    $student->user->email ?? '',
                    $primaryGuardian ? $primaryGuardian->first_name . ' ' . $primaryGuardian->father_name : '',
                    $primaryGuardian->phone ?? '',
                    $primaryGuardian->email ?? '',
                    $primaryGuardian->relationship ?? '',
                    $secondaryGuardian ? $secondaryGuardian->first_name . ' ' . $secondaryGuardian->father_name : '',
                    $secondaryGuardian->phone ?? '',
                    $secondaryGuardian->email ?? '',
                    $secondaryGuardian->relationship ?? '',
                    $student->medicalInfo->blood_group ?? '',
                    $student->medicalInfo->allergies ?? '',
                    $student->medicalInfo->medical_issues ?? '',
                    $student->transportation ? ($student->transportation->driver_first_name . ' ' . $student->transportation->driver_father_name) : '',
                    $student->transportation->route ?? '',
                    $student->is_active ? 'Active' : 'Inactive',
                ];
                
                fputcsv($file, $row);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function toggleBlock(Student $student)
    {
        $student->is_active = !$student->is_active;
        $student->save();

        // Also toggle user account status
        if ($student->user) {
            // Note: Laravel doesn't have a built-in 'is_active' on users table
            // We'll just use the student's is_active status
            // If you want to prevent login, you could delete the user's sessions
        }

        $status = $student->is_active ? 'unblocked' : 'blocked';
        return redirect()->back()->with('success', "Student has been {$status} successfully.");
    }

    public function transferForm(Student $student)
    {
        $student->load('enrollments.section.gradeLevel');
        $currentEnrollment = $student->enrollments()->whereNull('end_date')->first();
        
        $activeYear = AcademicYear::where('is_active', true)->first();
        $sections = Section::with('gradeLevel.division')
            ->where('is_active', true)
            ->when($activeYear, function($q) use ($activeYear) {
                return $q->where('academic_year_id', $activeYear->id);
            })
            ->get();

        return view('admin.students.transfer', compact('student', 'currentEnrollment', 'sections', 'activeYear'));
    }

    public function transfer(Request $request, Student $student)
    {
        $request->validate([
            'new_section_id' => 'required|exists:sections,id',
            'transfer_date' => 'required|date',
            'reason' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            // End current enrollment
            $currentEnrollment = $student->enrollments()->whereNull('end_date')->first();
            
            if (!$currentEnrollment) {
                return back()->with('error', 'Student has no active enrollment.');
            }

            if ($currentEnrollment->section_id == $request->new_section_id) {
                return back()->with('error', 'Student is already in this section.');
            }

            $currentEnrollment->update([
                'end_date' => $request->transfer_date,
                'status' => 'transferred',
            ]);

            // Create new enrollment
            $newSection = Section::findOrFail($request->new_section_id);
            $student->enrollments()->create([
                'section_id' => $request->new_section_id,
                'academic_year_id' => $newSection->academic_year_id,
                'enrollment_date' => $request->transfer_date,
                'status' => 'active',
            ]);

            DB::commit();
            return redirect()->route('admin.students.show', $student)
                ->with('success', 'Student transferred successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Transfer failed: ' . $e->getMessage());
        }
    }

    public function assignElectivesForm(Student $student)
    {
        $student->load(['enrollments' => function($q) {
            $q->whereNull('end_date')->latest(); // Active enrollment
        }, 'enrollments.section.gradeLevel.subjects']);

        $currentEnrollment = $student->enrollments->first();

        if (!$currentEnrollment) {
            return back()->with('error', 'Student must have an active enrollment to assign electives.');
        }

        $academicYearId = $currentEnrollment->academic_year_id;
        
        // Get elective subjects available for this student's grade level
        // We filter the subjects relation on the grade level model manually or query it
        $gradeLevel = $currentEnrollment->section->gradeLevel;
        
        // Assuming GradeLevel has 'subjects' relationship, we filter for electives
        // If relationship returns all subjects (pivot), we check is_elective column on Subject
        $availableElectives = $gradeLevel->subjects()->where('is_elective', true)->get();

        // Get currently assigned electives for this academic year
        $assignedElectiveIds = $student->electives()
            ->wherePivot('academic_year_id', $academicYearId)
            ->pluck('subjects.id')
            ->toArray();

        return view('admin.students.assign-electives', compact('student', 'currentEnrollment', 'availableElectives', 'assignedElectiveIds'));
    }

    public function storeElectives(Request $request, Student $student)
    {
        $request->validate([
            'electives' => 'array', // Can be empty if removing all
            'electives.*' => 'exists:subjects,id',
        ]);

        $currentEnrollment = $student->enrollments()->whereNull('end_date')->first();

        if (!$currentEnrollment) {
            return back()->with('error', 'Student must have an active enrollment.');
        }

        $academicYearId = $currentEnrollment->academic_year_id;

        // Sync electives for this academic year
        // We need to attach with academic_year_id
        // sync() supports pivot data
        
        $syncData = [];
        if ($request->has('electives')) {
            foreach ($request->electives as $subjectId) {
                $syncData[$subjectId] = ['academic_year_id' => $academicYearId];
            }
        }

        // We only want to sync for the CURRENT academic year. 
        // Sync replaces all records. If a student has records for PAST years, we shouldn't touch them.
        // sync() usually replaces all related records. 
        // To scoped sync, we can use detach() then attach(), or manual logic.
        // Let's remove current year electives first, then add new ones.
        
        DB::transaction(function () use ($student, $academicYearId, $syncData) {
            // Detach all electives for this student for this academic year
            DB::table('student_electives')
                ->where('student_id', $student->id)
                ->where('academic_year_id', $academicYearId)
                ->delete();

            // Attach new ones
            if (!empty($syncData)) {
                $student->electives()->attach($syncData);
            }
        });

        return redirect()->route('admin.students.show', $student)->with('success', 'Elective subjects updated successfully.');
    }

    public function destroy(Student $student)
    {
        // Delete photos if they exist
        if ($student->photo) {
            Storage::disk('public')->delete($student->photo);
        }

        // Delete guardian photos
        foreach ($student->guardians as $guardian) {
            if ($guardian->photo) {
                Storage::disk('public')->delete($guardian->photo);
            }
        }

        // Delete driver photo
        if ($student->transportation && $student->transportation->driver_photo) {
            Storage::disk('public')->delete($student->transportation->driver_photo);
        }

        $student->user->delete(); // This will cascade delete student
        
        return redirect()->route('admin.students.index')
            ->with('success', 'Student deleted successfully.');
    }
    public function import()
    {
        return view('admin.students.import');
    }

    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="students_import_template.csv"',
        ];

        $columns = [
            'first_name', 'father_name', 'grandfather_name', 'gender', 'date_of_birth',
            'birth_country', 'birth_city', 'nationality', 'language_spoken',
            'admission_number', 'admission_date', 'grade_level', 'section_name',
            'subcity', 'woreda', 'house_number', 'phone', 'email',
            'primary_guardian_first_name', 'primary_guardian_father_name', 'primary_guardian_grandfather_name',
            'primary_guardian_phone', 'primary_guardian_relationship', 'primary_guardian_email'
        ];

        $callback = function() use ($columns) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for Excel compatibility
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Force Excel to use comma as separator
            fwrite($file, "sep=,\n");
            
            fputcsv($file, $columns);
            
            // Add sample row
            fputcsv($file, [
                'Abebe', 'Kebede', 'Tesfaye', 'M', '2015-09-01',
                'Ethiopia', 'Addis Ababa', 'Ethiopian', 'Amharic',
                'STU001', '2023-09-01', 'Grade 1', 'A',
                'Bole', '03', '123', '0911234567', 'student@example.com',
                'Kebede', 'Tesfaye', 'Alemu',
                '0911987654', 'Father', 'parent@example.com'
            ]);
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('file');
        $path = $file->getRealPath();
        
        // Read file content
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        // Check for sep= line
        if (isset($lines[0]) && strpos($lines[0], 'sep=') === 0) {
            array_shift($lines);
        }
        
        if (empty($lines)) {
            return back()->with('error', 'The file is empty.');
        }

        // Detect delimiter
        $firstLine = $lines[0];
        $delimiter = ',';
        if (strpos($firstLine, ';') !== false) {
            $delimiter = ';';
        }

        // Remove BOM if present
        $bom = "\xEF\xBB\xBF";
        if (strpos($lines[0], $bom) === 0) {
            $lines[0] = substr($lines[0], 3);
        }

        // Parse CSV
        $data = array_map(function($line) use ($delimiter) {
            return str_getcsv($line, $delimiter);
        }, $lines);

        $header = array_shift($data);
        
        // Map header to index
        $header = array_map('trim', $header); // Trim all headers
        $headerMap = array_flip($header);
        
        // Required columns check
        $required = ['first_name', 'father_name', 'grandfather_name', 'gender', 'date_of_birth', 'admission_number', 'admission_date', 'grade_level', 'section_name', 'primary_guardian_first_name', 'primary_guardian_phone', 'primary_guardian_relationship'];
        foreach ($required as $col) {
            if (!isset($headerMap[$col])) {
                return back()->with('error', "Missing required column: $col. Found columns: " . implode(', ', $header));
            }
        }

        DB::beginTransaction();
        try {
            $count = 0;
            foreach ($data as $row) {
                // Skip empty rows
                if (empty($row) || empty($row[0])) continue;

                // Helper to get value by column name
                $val = function($col) use ($row, $headerMap) {
                    return isset($headerMap[$col]) ? trim($row[$headerMap[$col]]) : null;
                };

                // Find Section
                $gradeLevelName = $val('grade_level');
                $sectionName = $val('section_name');
                
                $section = \App\Models\Section::where('name', $sectionName)
                    ->whereHas('gradeLevel', function($q) use ($gradeLevelName) {
                        $q->where('name', $gradeLevelName);
                    })->first();

                if (!$section) {
                    throw new \Exception("Section '$sectionName' in Grade '$gradeLevelName' not found for student " . $val('first_name'));
                }

                // Create User
                $email = $val('email');
                if (empty($email)) {
                    $email = $val('admission_number') . '@student.renaissance.com';
                }
                
                // Check if user exists
                if (User::where('email', $email)->exists()) {
                    // Skip this row if user with this email already exists
                    continue; 
                }

                $user = User::create([
                    'name' => $val('first_name') . ' ' . $val('father_name') . ' ' . $val('grandfather_name'),
                    'email' => $email,
                    'password' => Hash::make('student123'),
                ]);
                $user->assignRole('Student');

                // Generate Student ID
                $studentId = $val('student_id');
                if (empty($studentId)) {
                    $year = date('Y');
                    $existingCount = Student::whereYear('created_at', $year)->count();
                    $studentId = 'STU-' . $year . '-' . str_pad($existingCount + 1 + $count, 4, '0', STR_PAD_LEFT);
                }

                // Helper to parse dates
                $dateVal = function($col) use ($val) {
                    $d = $val($col);
                    if (empty($d)) return null;
                    try {
                        return \Carbon\Carbon::parse($d)->format('Y-m-d');
                    } catch (\Exception $e) {
                        return null;
                    }
                };

                // Create Student
                $student = Student::create([
                    'user_id' => $user->id,
                    'student_id' => $studentId,
                    'first_name' => $val('first_name'),
                    'father_name' => $val('father_name'),
                    'grandfather_name' => $val('grandfather_name'),
                    'last_name' => $val('grandfather_name'), // Use grandfather name as last name
                    'gender' => $val('gender'),
                    'date_of_birth' => $dateVal('date_of_birth'),
                    'birth_country' => $val('birth_country'),
                    'birth_city' => $val('birth_city'),
                    'nationality' => $val('nationality') ?? 'Ethiopian',
                    'language_spoken' => $val('language_spoken'),
                    'admission_number' => $val('admission_number'),
                    'admission_date' => $dateVal('admission_date'),
                    'phone' => $val('phone'),
                    'subcity' => $val('subcity'),
                    'woreda' => $val('woreda'),
                    'house_number' => $val('house_number'),
                    'is_active' => true,
                ]);

                // Enroll in Section
                // Assuming current academic year is active. We need to find active year.
                $activeYear = \App\Models\AcademicYear::where('is_active', true)->first();
                if ($activeYear) {
                    if ($gradeLevelName && $sectionName) {
                        $section = \App\Models\Section::where('name', $sectionName)
                            ->whereHas('gradeLevel', function($q) use ($gradeLevelName) {
                                $q->where('name', $gradeLevelName);
                            })
                            ->where('academic_year_id', $activeYear->id)
                            ->first();

                        if (!$section) {
                            throw new \Exception("Section '$sectionName' in Grade '$gradeLevelName' not found for student " . $val('first_name'));
                        }

                        $student->enrollments()->create([
                            'section_id' => $section->id,
                            'academic_year_id' => $activeYear->id,
                            'status' => 'active',
                            'enrollment_date' => $dateVal('admission_date'),
                        ]);
                    }
                }

                // Create Primary Guardian
                if ($val('primary_guardian_first_name')) {
                    $student->guardians()->create([
                        'first_name' => $val('primary_guardian_first_name'),
                        'father_name' => $val('primary_guardian_father_name'),
                        'grandfather_name' => $val('primary_guardian_grandfather_name'),
                        'phone' => $val('primary_guardian_phone'),
                        'email' => $val('primary_guardian_email'),
                        'relationship' => $val('primary_guardian_relationship'),
                        'guardian_type' => 'primary',
                        'is_primary' => true,
                    ]);
                }

                $count++;
            }
            
            DB::commit();
            return redirect()->route('admin.students.index')->with('success', "$count students imported successfully.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }
}

