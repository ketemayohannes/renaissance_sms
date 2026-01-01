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
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;

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
        $startTime = microtime(true);
        Log::info('EmployeeController@index started');
        $query = Employee::with('user');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('middle_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('employee_id', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('designation')) {
            $query->where('designation', $request->designation);
        }

        if ($request->filled('department')) {
            $query->where('department', $request->department);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('staff_category')) {
            $query->where('staff_category', $request->staff_category);
        }

        $employees = $query->latest()->paginate(15)->withQueryString();
        
        $stats = \App\Helpers\CachedData::employeeStats();

        $endTime = microtime(true);
        Log::info('EmployeeController@index finished', ['execution_time' => $endTime - $startTime]);

        $queryCount = count(DB::getQueryLog());
        return view('admin.employees.index', compact('employees', 'stats', 'queryCount'));
    }

    public function create()
    {
        return view('admin.employees.create');
    }

    public function store(StoreEmployeeRequest $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                // 1. Create User
                $user = User::create([
                    'name' => trim("{$request->first_name} {$request->middle_name} {$request->last_name}"),
                    'email' => $request->email,
                    'password' => Hash::make('password'), // Static for now, can be random
                ]);

                // 2. Assign Role
                if ($request->designation === 'Teacher') {
                    $user->assignRole('Teacher');
                } else {
                    $user->assignRole('Staff');
                }

                // 3. Handle Photo
                $photoPath = null;
                if ($request->hasFile('photo')) {
                    $photoPath = $request->file('photo')->store('employees/photos', 'public');
                }

                // 4. Generate Employee ID
                $nextValue = SystemCounter::next('employee_id_sequence', Employee::count());
                $year = date('Y');
                $employeeId = 'EMP-' . $year . '-' . str_pad($nextValue, 4, '0', STR_PAD_LEFT);

                // 5. Create Employee
                $employee = Employee::create(array_merge($request->validated(), [
                    'user_id' => $user->id,
                    'employee_id' => $employeeId,
                    'photo' => $photoPath,
                    'status' => 'active',
                ]));

                return redirect()->route('admin.employees.index')
                    ->with('success', "Employee registered successfully. ID: {$employee->employee_id}");
            });
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Registration failed: ' . $e->getMessage());
        }
    }

    public function show(Employee $employee)
    {
        $employee->load('user');
        return view('admin.employees.show', compact('employee'));
    }

    public function edit(Employee $employee)
    {
        return view('admin.employees.edit', compact('employee'));
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee)
    {
        try {
            DB::transaction(function () use ($request, $employee) {
                $data = $request->validated();

                if ($request->hasFile('photo')) {
                    if ($employee->photo) {
                        Storage::disk('public')->delete($employee->photo);
                    }
                    $data['photo'] = $request->file('photo')->store('employees/photos', 'public');
                }

                $employee->update($data);

                // Sync user info
                $employee->user->update([
                    'name' => trim("{$employee->first_name} {$employee->middle_name} {$employee->last_name}"),
                    'email' => $employee->email,
                ]);
            });

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

    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="staff_import_template.csv"',
        ];

        $columns = [
            'first_name', 'middle_name', 'last_name', 'gender', 'date_of_birth', 'marital_status',
            'email', 'phone', 'address', 'region', 'zone', 'woreda',
            'national_id', 'tin', 'pension_number', 'employee_id',
            'staff_category', 'designation', 'department', 'joining_date', 'basic_salary',
            'employment_type', 'emergency_contact_name', 'emergency_contact_phone',
            'bank_name', 'account_number', 'teacher_rank', 'qualification_level',
            'specialization', 'periods_per_week'
        ];

        $callback = function() use ($columns) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fwrite($file, "sep=,\n");
            fputcsv($file, $columns);

            fputcsv($file, [
                'Abebe', 'Kebede', 'Tesfaye', 'M', '1985-05-15', 'married',
                'abebe.kebede@school.com', '0911223344', 'Addis Ababa', 'Addis Ababa', 'Bole', 'Woreda 03',
                'ID123456', 'TIN789', 'PEN1122', 'EMP-2025-0001',
                'academic', 'Teacher', 'Academic', '2023-09-01', '15000.00',
                'full_time', 'Kebede Tesfaye', '0911556677',
                'Commercial Bank of Ethiopia', '1000123456789', 'Senior Teacher', 'Masters',
                'Mathematics', '18'
            ]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function upload(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:csv,txt|max:2048']);

        ini_set('auto_detect_line_endings', true);
        $lines = file($request->file('file')->getRealPath(), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (empty($lines)) return back()->with('error', 'The file is empty.');

        if (strpos($lines[0], 'sep=') === 0) array_shift($lines);
        $delimiter = strpos($lines[0], ';') !== false ? ';' : ',';
        if (strpos($lines[0], "\xEF\xBB\xBF") === 0) $lines[0] = substr($lines[0], 3);

        $data = array_map(fn($line) => str_getcsv($line, $delimiter), $lines);
        $header = array_map('trim', array_shift($data));
        $headerMap = array_flip($header);

        $rows = array_map(function($row) use ($headerMap) {
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
                'staff_category' => $val('staff_category'),
                'designation' => $val('designation'),
                'department' => $val('department'),
                'joining_date' => $val('joining_date'),
                'basic_salary' => $val('basic_salary'),
                'employment_type' => $val('employment_type'),
                'emergency_contact_name' => $val('emergency_contact_name'),
                'emergency_contact_phone' => $val('emergency_contact_phone'),
                'bank_name' => $val('bank_name'),
                'account_number' => $val('account_number'),
                'teacher_rank' => $val('teacher_rank'),
                'qualification_level' => $val('qualification_level'),
                'specialization' => $val('specialization'),
                'periods_per_week' => $val('periods_per_week'),
            ];
        }, $data);

        $result = $this->employeeService->bulkImport($rows);

        $message = "Import completed. Success: {$result['success']}, Skipped: {$result['skipped']}.";
        $redirect = redirect()->route('admin.employees.index')->with('success', $message);
        
        if (count($result['errors']) > 0) {
            $redirect->with('import_errors', $result['errors']);
        }

        return $redirect;
    }
}
