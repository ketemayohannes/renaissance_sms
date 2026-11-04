<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\ImportStudentsRequest;
use App\Models\AcademicYear;
use App\Models\Division;
use App\Models\GradeLevel;
use App\Models\Student;
use App\Services\StudentService;

class StudentImportController extends Controller
{
    public function __construct(protected StudentService $studentService) {}

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
            'primary_guardian_phone', 'primary_guardian_relationship', 'primary_guardian_email',
        ];

        $callback = function () use ($columns) {
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
                '0911987654', 'Father', 'parent@example.com',
            ]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function downloadQuickTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="students_quick_import_template.csv"',
        ];

        $columns = ['first_name', 'father_name', 'grandfather_name', 'gender', 'grade_level', 'section_name'];

        $callback = function () use ($columns) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fwrite($file, "sep=,\n");
            fputcsv($file, $columns);
            fputcsv($file, ['Abebe',  'Kebede', 'Tesfaye', 'M', 'Grade 1', 'A']);
            fputcsv($file, ['Tigist', 'Alemu',  'Bekele',  'F', 'Grade 2', 'B']);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function quickUpload(ImportStudentsRequest $request)
    {
        $activeYear = AcademicYear::where('is_active', true)->first();
        if (! $activeYear) {
            return back()->with('error', 'No active academic year found.');
        }

        ini_set('auto_detect_line_endings', true);
        $lines = file($request->file('file')->getRealPath(), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (empty($lines)) {
            return back()->with('error', 'The file is empty.');
        }

        if (strpos($lines[0], 'sep=') === 0) {
            array_shift($lines);
        }
        $delimiter = strpos($lines[0], ';') !== false ? ';' : ',';
        if (strpos($lines[0], "\xEF\xBB\xBF") === 0) {
            $lines[0] = substr($lines[0], 3);
        }

        $data = array_map(fn ($line) => str_getcsv($line, $delimiter), $lines);
        $header = array_map('trim', array_shift($data));
        $headerMap = array_flip($header);

        $today = now()->format('Y-m-d');

        $gradeLevels = GradeLevel::with('division')->get();
        $gradeLevelMap = [];
        foreach ($gradeLevels as $gl) {
            $gradeLevelMap[strtolower($gl->name)] = $gl;
        }

        $getCounter = function ($prefix, $default = 1) {
            $last = Student::where('admission_number', 'like', $prefix.'%')
                ->orderByRaw('LENGTH(admission_number) DESC, admission_number DESC')
                ->value('admission_number');
            if ($last) {
                preg_match('/'.preg_quote($prefix, '/').'(\d+)/', $last, $matches);
                if (! empty($matches[1])) {
                    return (int) $matches[1] + 1;
                }
            }

            return $default;
        };

        $kgCounter = $getCounter('RISKG');
        $esCounter = $getCounter('RISEL');
        $hsCounter = $getCounter('RISHS');

        $rows = array_map(function ($row) use ($headerMap, $today, $gradeLevelMap, &$kgCounter, &$esCounter, &$hsCounter) {
            $val = fn ($key) => isset($headerMap[$key]) && isset($row[$headerMap[$key]])
                ? trim($row[$headerMap[$key]])
                : null;

            $gradeLevelName = $val('grade_level');
            $divisionCode = 'ES'; // Default fallback division code
            if ($gradeLevelName) {
                $glKey = strtolower(trim($gradeLevelName));
                $gl = $gradeLevelMap[$glKey] ?? null;
                if (! $gl && ! str_contains($glKey, 'grade') && ! str_contains($glKey, 'kg')) {
                    $gl = $gradeLevelMap['grade '.$glKey] ?? null;
                }
                if ($gl && $gl->division) {
                    $divisionCode = $gl->division->code;
                }
            }

            if ($divisionCode === 'KG') {
                $admissionNumber = 'RISKG'.str_pad($kgCounter++, 3, '0', STR_PAD_LEFT);
                $dob = now()->subYears(5)->format('Y-m-d');
            } elseif ($divisionCode === 'HS') {
                $admissionNumber = 'RISHS'.str_pad($hsCounter++, 3, '0', STR_PAD_LEFT);
                $dob = now()->subYears(16)->format('Y-m-d');
            } else {
                // Default to ES (Elementary)
                $admissionNumber = 'RISEL'.str_pad($esCounter++, 4, '0', STR_PAD_LEFT);
                $dob = now()->subYears(10)->format('Y-m-d');
            }

            return [
                'first_name' => $val('first_name'),
                'father_name' => $val('father_name'),
                'grandfather_name' => $val('grandfather_name'),
                'gender' => $val('gender'),
                'date_of_birth' => $dob,
                'birth_country' => null,
                'birth_city' => null,
                'nationality' => 'Ethiopian',
                'language_spoken' => null,
                'admission_number' => $admissionNumber,
                'admission_date' => $today,
                'grade_level' => $gradeLevelName,
                'section_name' => $val('section_name'),
                'phone' => null,
                'subcity' => null,
                'woreda' => null,
                'house_number' => null,
                'email' => null,
                'student_id' => null,
                'primary_guardian_first_name' => null,
                'primary_guardian_father_name' => null,
                'primary_guardian_grandfather_name' => null,
                'primary_guardian_phone' => null,
                'primary_guardian_email' => null,
                'primary_guardian_relationship' => null,
            ];
        }, $data);

        $result = $this->studentService->bulkImport($rows, $activeYear);

        $message = "Quick Import completed. {$result['success']} student(s) added, {$result['skipped']} skipped.";
        $redirect = redirect()->route('admin.students.index')->with('success', $message);

        if (count($result['errors']) > 0) {
            $redirect->with('import_errors', $result['errors']);
        }

        return $redirect;
    }

    public function upload(ImportStudentsRequest $request)
    {
        $activeYear = AcademicYear::where('is_active', true)->first();
        if (! $activeYear) {
            return back()->with('error', 'No active academic year found.');
        }

        ini_set('auto_detect_line_endings', true);
        $lines = file($request->file('file')->getRealPath(), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (empty($lines)) {
            return back()->with('error', 'The file is empty.');
        }

        if (strpos($lines[0], 'sep=') === 0) {
            array_shift($lines);
        }
        $delimiter = strpos($lines[0], ';') !== false ? ';' : ',';
        if (strpos($lines[0], "\xEF\xBB\xBF") === 0) {
            $lines[0] = substr($lines[0], 3);
        }

        $data = array_map(fn ($line) => str_getcsv($line, $delimiter), $lines);
        $header = array_map('trim', array_shift($data));
        $headerMap = array_flip($header);

        $rows = array_map(function ($row) use ($headerMap) {
            $val = fn ($key) => isset($headerMap[$key]) && isset($row[$headerMap[$key]]) ? trim($row[$headerMap[$key]]) : null;

            return [
                'first_name' => $val('first_name'),
                'father_name' => $val('father_name'),
                'grandfather_name' => $val('grandfather_name'),
                'gender' => $val('gender'),
                'date_of_birth' => $val('date_of_birth'),
                'birth_country' => $val('birth_country'),
                'birth_city' => $val('birth_city'),
                'nationality' => $val('nationality'),
                'language_spoken' => $val('language_spoken'),
                'admission_number' => $val('admission_number'),
                'admission_date' => $val('admission_date'),
                'grade_level' => $val('grade_level'),
                'section_name' => $val('section_name'),
                'phone' => $val('phone'),
                'subcity' => $val('subcity'),
                'woreda' => $val('woreda'),
                'house_number' => $val('house_number'),
                'email' => $val('email'),
                'student_id' => $val('student_id'),
                'primary_guardian_first_name' => $val('primary_guardian_first_name'),
                'primary_guardian_father_name' => $val('primary_guardian_father_name'),
                'primary_guardian_grandfather_name' => $val('primary_guardian_grandfather_name'),
                'primary_guardian_phone' => $val('primary_guardian_phone'),
                'primary_guardian_email' => $val('primary_guardian_email'),
                'primary_guardian_relationship' => $val('primary_guardian_relationship'),
            ];
        }, $data);

        $result = $this->studentService->bulkImport($rows, $activeYear);

        $message = "Import completed. Success: {$result['success']}, Skipped: {$result['skipped']}.";
        $redirect = redirect()->route('admin.students.index')->with('success', $message);

        if (count($result['errors']) > 0) {
            $redirect->with('import_errors', $result['errors']);
        }

        return $redirect;
    }
}
