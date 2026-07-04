<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Models\Employee;
use App\Models\User;
use App\Models\SystemCounter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\AcademicYear;
use App\Models\Division;
use App\Models\Section;
use App\Models\Subject;
use App\Services\EmployeeService;

class EmployeeController extends Controller
{
    protected $employeeService;

    public function __construct(EmployeeService $employeeService)
    {
        $this->employeeService = $employeeService;
    }
    public function index(Request $request)
    {
        DB::enableQueryLog();
        $query = Employee::with(['user', 'division', 'user.roles']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('middle_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('employee_id', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhereHas('user.roles', function($rq) use ($search) {
                      $rq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('role')) {
            $roleName = $request->role;
            $query->whereHas('user.roles', function($q) use ($roleName) {
                $q->where('name', $roleName);
            });
        } elseif ($request->filled('designation')) {
            $query->where('designation', $request->designation);
        }

        if ($request->filled('department')) {
            $query->where('department', $request->department);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('staff_category')) {
            $category = $request->staff_category;
            $query->whereHas('user.roles', function($q) use ($category) {
                $q->where('category', $category);
            });
        }

        $employees = $query->latest()->paginate(15)->withQueryString();
        $stats = \App\Helpers\CachedData::employeeStats();
        $roles = \Spatie\Permission\Models\Role::all();
        $queryCount = count(DB::getQueryLog());

        return view('admin.employees.index', compact('employees', 'stats', 'roles', 'queryCount'));
    }

    public function create()
    {
        $sections = \App\Models\Section::with('gradeLevel')->where('is_active', true)->get();
        $subjects = \App\Models\Subject::where('is_active', true)->get();
        $divisions = \App\Models\Division::where('is_active', true)->get();
        $designations = Employee::DESIGNATIONS;
        $rolesByCategory = $this->employeeService->getRolesByCategory();

        return view('admin.employees.create', compact('sections', 'subjects', 'divisions', 'designations', 'rolesByCategory'));
    }

    public function store(StoreEmployeeRequest $request)
    {
        try {
            $data = $request->validated();

            // Handle Photo
            if ($request->hasFile('photo')) {
                $data['photo'] = $request->file('photo')->store('employees/photos', 'public');
            }

            $employee = $this->employeeService->createEmployee($data);

            // Handle Assignments for Teachers separately to keep service clean
            if ($request->filled('assignments') && $request->role === 'Teacher') {
                $activeYear = AcademicYear::where('is_active', true)->first();
                if ($activeYear) {
                    foreach ($request->assignments as $assignment) {
                        \App\Models\TeacherAssignment::create([
                            'teacher_id' => $employee->user_id,
                            'section_id' => $assignment['section_id'],
                            'subject_id' => $assignment['subject_id'],
                            'academic_year_id' => $activeYear->id,
                        ]);
                    }
                }
            }

            return redirect()->route('admin.employees.index')
                ->with('success', "Employee registered successfully. ID: {$employee->employee_id}");
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Registration failed: ' . $e->getMessage());
        }
    }

    public function show(Employee $employee)
    {
        $employee->load([
            'user.roles', 
            'user.teacherAssignments.section.gradeLevel', 
            'user.teacherAssignments.subject', 
            'division', 
            'academicDetails', 
            'administrativeDetails', 
            'documents'
        ]);
        return view('admin.employees.show', compact('employee'));
    }

    public function edit(Employee $employee)
    {
        $sections = \App\Models\Section::with('gradeLevel')->where('is_active', true)->get();
        $subjects = \App\Models\Subject::where('is_active', true)->get();
        $divisions = \App\Models\Division::where('is_active', true)->get();
        $designations = Employee::DESIGNATIONS;
        $rolesByCategory = $this->employeeService->getRolesByCategory();

        $employee->load(['user.teacherAssignments', 'academicDetails', 'administrativeDetails']);
        return view('admin.employees.edit', compact('employee', 'sections', 'subjects', 'divisions', 'designations', 'rolesByCategory'));
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee)
    {
        try {
            $data = $request->validated();

            if ($request->hasFile('photo')) {
                if ($employee->photo) {
                    Storage::disk('public')->delete($employee->photo);
                }
                $data['photo'] = $request->file('photo')->store('employees/photos', 'public');
            }

            $this->employeeService->updateEmployee($employee, $data);

            // Update Assignments separately
            if ($request->filled('assignments') && ($request->role === 'Teacher' || $employee->user->hasRole('Teacher'))) {
                $activeYear = AcademicYear::where('is_active', true)->first();
                if ($activeYear) {
                    \App\Models\TeacherAssignment::where('teacher_id', $employee->user_id)
                        ->where('academic_year_id', $activeYear->id)
                        ->delete();

                    foreach ($request->assignments as $assignment) {
                        \App\Models\TeacherAssignment::create([
                            'teacher_id' => $employee->user_id,
                            'section_id' => $assignment['section_id'],
                            'subject_id' => $assignment['subject_id'],
                            'academic_year_id' => $activeYear->id,
                        ]);
                    }
                }
            }

            return redirect()->route('admin.employees.show', $employee)
                ->with('success', 'Employee updated successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Update failed: ' . $e->getMessage());
        }
    }

    public function destroy(Employee $employee)
    {
        try {
            DB::transaction(function() use ($employee) {
                if ($employee->user) {
                    $employee->user->delete();
                }
                $employee->delete();
            });

            return redirect()->route('admin.employees.index')
                ->with('success', 'Employee deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Deletion failed: ' . $e->getMessage());
        }
    }

    public function import()
    {
        return view('admin.employees.import');
    }

    public function downloadAcademicTemplate()
    {
        return $this->generateTemplate('academic');
    }

    public function downloadAdministrativeTemplate()
    {
        return $this->generateTemplate('administrative');
    }

    private function generateTemplate($category)
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        
        // Sheet 1: Data Entry
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Staff Data');
        
        $baseColumns = [
            'first_name', 'middle_name', 'last_name', 'gender', 'date_of_birth', 'marital_status',
            'email', 'phone', 'address', 'region', 'zone', 'woreda',
            'national_id', 'tin', 'pension_number', 'employee_id',
            'staff_category', 'designation'
        ];

        $categoryColumns = [
            'academic' => [
                'qualification_level', 'specialization', 
                'joining_date', 'basic_salary', 'employment_type', 'division_id'
            ],
            'administrative' => [
                'system_access_roles', 'qualification_level', 'specialization', 
                'joining_date', 'basic_salary', 'employment_type', 'division_id'
            ]
        ];

        $columns = array_merge($baseColumns, $categoryColumns[$category], [
            'emergency_contact_name', 'emergency_contact_phone', 'bank_name', 'account_number'
        ]);
        
        // Header row
        $sheet->fromArray($columns, null, 'A1');
        
        // Style header row
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => ($category === 'academic' ? '4F46E5' : '10B981')]],
        ];
        $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($columns));
        $sheet->getStyle('A1:' . $lastCol . '1')->applyFromArray($headerStyle);
        
        // Example row
        $exampleRow = [
            'Abebe', 'Kebede', 'Tesfaye', 'M', '1985-05-15', 'married',
            'abebe.kebede.test@school.com', '0911223344', 'Addis Ababa', 'Addis Ababa', 'Bole', 'Woreda 03',
            'ID123456', 'TIN789', 'PEN1122', 'EMP-' . date('Y') . '-0001',
            $category, ($category === 'academic' ? 'Teacher' : 'Secretary')
        ];

        if ($category === 'academic') {
            $exampleRow = array_merge($exampleRow, ['Masters', 'Mathematics']);
        } else {
            $exampleRow = array_merge($exampleRow, ['Registrar, Finance', 'Bachelors', 'Accounting']);
        }

        $exampleRow = array_merge($exampleRow, [
            date('Y-m-d'), '15000.00', 'full_time', '1',
            'Kebede Tesfaye', '0911556677', 'Commercial Bank of Ethiopia', '1000123456789'
        ]);

        $sheet->fromArray($exampleRow, null, 'A2');
        
        // Auto-size columns
        foreach (range(1, count($columns)) as $colIndex) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Sheet 2: Legends
        $legendSheet = $spreadsheet->createSheet();
        $legendSheet->setTitle('Legends');
        
        // Division Legend
        $legendSheet->setCellValue('A1', 'DIVISION LEGEND');
        $legendSheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $legendSheet->setCellValue('A2', 'division_id');
        $legendSheet->setCellValue('B2', 'Division Name');
        $legendSheet->getStyle('A2:B2')->getFont()->setBold(true);
        
        $divisions = \App\Models\Division::orderBy('sort_order')->get();
        $row = 3;
        foreach ($divisions as $div) {
            $legendSheet->setCellValue('A' . $row, $div->id);
            $legendSheet->setCellValue('B' . $row, $div->name);
            $row++;
        }
        $legendSheet->setCellValue('A' . $row, '(blank)');
        $legendSheet->setCellValue('B' . $row, 'Global Access (All Divisions)');
        
        // Job Designation Legend (Roles)
        $legendSheet->setCellValue('D1', strtoupper($category) . ' ROLES LEGEND');
        $legendSheet->getStyle('D1')->getFont()->setBold(true)->setSize(14);
        $legendSheet->setCellValue('D2', 'Role Name');
        $legendSheet->setCellValue('E2', 'Description');
        $legendSheet->getStyle('D2:E2')->getFont()->setBold(true);
        
        $roles = \Spatie\Permission\Models\Role::where('category', $category)->orderBy('name')->get();
        $row = 3;
        foreach ($roles as $role) {
            $legendSheet->setCellValue('D' . $row, $role->name);
            $legendSheet->setCellValue('E' . $row, $role->name . ' permissions will be assigned');
            $row++;
        }
        
        // Staff Category Legend
        $legendSheet->setCellValue('G1', 'GENERAL LEGENDS');
        $legendSheet->getStyle('G1')->getFont()->setBold(true)->setSize(14);
        
        $legendSheet->setCellValue('G2', 'employment_type');
        $legendSheet->setCellValue('H2', 'Value');
        $legendSheet->getStyle('G2:H2')->getFont()->setBold(true);
        $legendSheet->setCellValue('G3', 'Full Time'); $legendSheet->setCellValue('H3', 'full_time');
        $legendSheet->setCellValue('G4', 'Part Time'); $legendSheet->setCellValue('H4', 'part_time');
        $legendSheet->setCellValue('G5', 'Contract'); $legendSheet->setCellValue('H5', 'contract');

        $legendSheet->setCellValue('G7', 'gender');
        $legendSheet->setCellValue('H7', 'Value');
        $legendSheet->getStyle('G7:H7')->getFont()->setBold(true);
        $legendSheet->setCellValue('G8', 'Male'); $legendSheet->setCellValue('H8', 'M');
        $legendSheet->setCellValue('G9', 'Female'); $legendSheet->setCellValue('H9', 'F');
        
        // Auto-size legend columns
        foreach (['A', 'B', 'D', 'E', 'G', 'H'] as $col) {
            $legendSheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Set first sheet as active
        $spreadsheet->setActiveSheetIndex(0);
        
        // Generate file
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'staff_import_' . $category . '_template.xlsx';
        
        $tempFile = tempnam(sys_get_temp_dir(), 'xlsx');
        $writer->save($tempFile);
        
        return response()->download($tempFile, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    public function upload(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:csv,txt,xlsx,xls|max:2048']);

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());
        
        if (in_array($extension, ['xlsx', 'xls'])) {
            // Handle Excel file
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getRealPath());
            $worksheet = $spreadsheet->getActiveSheet();
            $data = $worksheet->toArray();
            
            if (empty($data)) return back()->with('error', 'The file is empty.');
            
            $header = array_map('trim', array_shift($data));
            $headerMap = array_flip($header);
            
            $rows = array_filter(array_map(function($row) use ($headerMap, $request) {
                // Skip empty rows
                if (empty(array_filter($row))) return null;
                
                $val = fn($key) => isset($headerMap[$key]) && isset($row[$headerMap[$key]]) ? trim($row[$headerMap[$key]] ?? '') : null;
                return [
                    'first_name' => $val('first_name'),
                    'middle_name' => $val('middle_name'),
                    'last_name' => $val('last_name'),
                    'gender' => $val('gender'),
                    'date_of_birth' => $val('date_of_birth'),
                    'marital_status' => $val('marital_status'),
                    'email' => $val('email'),
                    'phone' => $val('phone'),
                    'address' => $val('address'),
                    'region' => $val('region'),
                    'zone' => $val('zone'),
                    'woreda' => $val('woreda'),
                    'national_id' => $val('national_id'),
                    'tin' => $val('tin'),
                    'pension_number' => $val('pension_number'),
                    'employee_id' => $val('employee_id'),
                    'staff_category' => $val('staff_category') ?? $request->staff_category,
                    'designation' => $val('designation'),
                    'department' => $val('department'),
                    'joining_date' => $val('joining_date'),
                    'basic_salary' => $val('basic_salary'),
                    'employment_type' => $val('employment_type'),
                    'emergency_contact_name' => $val('emergency_contact_name'),
                    'emergency_contact_phone' => $val('emergency_contact_phone'),
                    'bank_name' => $val('bank_name'),
                    'account_number' => $val('account_number'),
                    'qualification_level' => $val('qualification_level'),
                    'specialization' => $val('specialization'),
                    'division_id' => $val('division_id'),
                ];
            }, $data));
        } else {
            // Handle CSV file
            ini_set('auto_detect_line_endings', true);
            $lines = file($file->getRealPath(), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            if (empty($lines)) return back()->with('error', 'The file is empty.');

            if (strpos($lines[0], 'sep=') === 0) array_shift($lines);
            $delimiter = strpos($lines[0], ';') !== false ? ';' : ',';
            if (strpos($lines[0], "\xEF\xBB\xBF") === 0) $lines[0] = substr($lines[0], 3);

            $data = array_map(fn($line) => str_getcsv($line, $delimiter), $lines);
            $header = array_map('trim', array_shift($data));
            $headerMap = array_flip($header);

            $rows = array_map(function($row) use ($headerMap, $request) {
                $val = fn($key) => isset($headerMap[$key]) && isset($row[$headerMap[$key]]) ? trim($row[$headerMap[$key]]) : null;
                return [
                    'first_name' => $val('first_name'),
                    'middle_name' => $val('middle_name'),
                    'last_name' => $val('last_name'),
                    'gender' => $val('gender'),
                    'date_of_birth' => $val('date_of_birth'),
                    'marital_status' => $val('marital_status'),
                    'email' => $val('email'),
                    'phone' => $val('phone'),
                    'address' => $val('address'),
                    'region' => $val('region'),
                    'zone' => $val('zone'),
                    'woreda' => $val('woreda'),
                    'national_id' => $val('national_id'),
                    'tin' => $val('tin'),
                    'pension_number' => $val('pension_number'),
                    'employee_id' => $val('employee_id'),
                    'staff_category' => $val('staff_category') ?? $request->staff_category,
                    'designation' => $val('designation'),
                    'department' => $val('department'),
                    'joining_date' => $val('joining_date'),
                    'basic_salary' => $val('basic_salary'),
                    'employment_type' => $val('employment_type'),
                    'emergency_contact_name' => $val('emergency_contact_name'),
                    'emergency_contact_phone' => $val('emergency_contact_phone'),
                    'bank_name' => $val('bank_name'),
                    'account_number' => $val('account_number'),
                    'qualification_level' => $val('qualification_level'),
                    'specialization' => $val('specialization'),
                    'division_id' => $val('division_id'),
                ];
            }, $data);
        }

        $result = $this->employeeService->bulkImport($rows);

        $message = "Import completed. Success: {$result['success']}, Skipped: {$result['skipped']}.";
        
        if (count($result['errors']) > 0) {
            return redirect()->route('admin.employees.index')
                ->with('success', $message)
                ->with('import_errors', $result['errors']);
        }
        return redirect()->route('admin.employees.index')->with('success', $message);
    }

    public function resetPassword(Employee $employee)
    {
        if (!$employee->user_id || !$employee->user) {
            return back()->with('error', 'Staff member does not have a portal account.');
        }

        $password = Str::random(10);
        $employee->user->update([
            'password' => Hash::make($password),
            'temp_password' => $password,
        ]);

        return back()->with('success', 'Password reset successfully. New Password: ' . $password);
    }

    public function toggleStatus(Employee $employee)
    {
        $newStatus = $employee->status === 'active' ? 'resigned' : 'active';
        $employee->update(['status' => $newStatus]);

        $message = $newStatus === 'active' ? 'Staff member activated.' : 'Staff member deactivated.';
        return back()->with('success', $message);
    }

    public function downloadDocument(EmployeeDocument $document)
    {
        // New documents live on the private disk; fall back to the legacy public disk.
        if (Storage::disk('local')->exists($document->file_path)) {
            return Storage::disk('local')->download($document->file_path, $document->name);
        }

        if (Storage::disk('public')->exists($document->file_path)) {
            return Storage::disk('public')->download($document->file_path, $document->name);
        }

        abort(404);
    }

    public function deleteDocument(EmployeeDocument $document)
    {
        if (Storage::disk('local')->exists($document->file_path)) {
            Storage::disk('local')->delete($document->file_path);
        } elseif (Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();

        return back()->with('success', 'Document deleted successfully.');
    }
}
