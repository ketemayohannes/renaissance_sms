<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\GradeLevel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubjectController extends Controller
{
    public function index()
    {
        $subjects = Subject::orderBy('sort_order')->get();
        return view('admin.subjects.index', compact('subjects'));
    }

    public function create()
    {
        return view('admin.subjects.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:20|unique:subjects,code',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ]);

        $data = $request->all();
        $data['is_elective'] = $request->has('is_elective');
        
        Subject::create($data);

        return redirect()->route('admin.subjects.index')
            ->with('success', 'Subject created successfully.');
    }

    public function edit(Subject $subject)
    {
        return view('admin.subjects.edit', compact('subject'));
    }

    public function update(Request $request, Subject $subject)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:20|unique:subjects,code,' . $subject->id,
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'is_elective' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $data = $request->all();
        $data['is_active'] = $request->has('is_active');
        $data['is_elective'] = $request->has('is_elective');

        $subject->update($data);

        return redirect()->route('admin.subjects.index')
            ->with('success', 'Subject updated successfully.');
    }

    public function destroy(Subject $subject)
    {
        $subject->delete();

        return redirect()->route('admin.subjects.index')
            ->with('success', 'Subject deleted successfully.');
    }

    public function reorder(Request $request)
    {
        $gradeLevels = GradeLevel::where('is_active', true)->orderBy('sort_order')->get();
        $selectedGradeId = $request->get('grade_level_id');
        $subjects = collect();

        if ($selectedGradeId) {
            $gradeLevel = GradeLevel::findOrFail($selectedGradeId);
            $subjects = $gradeLevel->subjects()->orderByPivot('sort_order')->get();
        }

        return view('admin.subjects.reorder', compact('subjects', 'gradeLevels', 'selectedGradeId'));
    }

    public function updateOrder(Request $request)
    {
        $request->validate([
            'grade_level_id' => 'required|exists:grade_levels,id',
            'orders' => 'required|array',
            'orders.*' => 'required|integer|min:0',
        ]);

        $gradeLevelId = $request->grade_level_id;

        foreach ($request->orders as $subjectId => $order) {
            DB::table('grade_level_subjects')
                ->where('grade_level_id', $gradeLevelId)
                ->where('subject_id', $subjectId)
                ->update(['sort_order' => $order]);
        }

        return redirect()->route('admin.subjects.reorder', ['grade_level_id' => $gradeLevelId])
            ->with('success', 'Subject order for the selected grade level updated successfully.');
    }

    public function import()
    {
        return view('admin.subjects.import');
    }

    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="subjects_import_template.csv"',
        ];

        $columns = ['name', 'code', 'description', 'is_elective', 'sort_order'];

        $callback = function() use ($columns) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM
            fwrite($file, "sep=,\n");
            fputcsv($file, $columns);
            
            // Sample rows
            fputcsv($file, ['Mathematics', 'MAT101', 'Core mathematics subject', '0', '1']);
            fputcsv($file, ['Art and Crafts', 'ART01', 'Elective art subject', '1', '10']);
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        try {
            $file = $request->file('file');
            $path = $file->getRealPath();
            
            ini_set('auto_detect_line_endings', true);
            $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            
            if (empty($lines)) {
                return back()->with('error', 'The file is empty.');
            }

            // Handle sep=, line if present
            if (strpos($lines[0], 'sep=') === 0) {
                array_shift($lines);
            }

            // Determine delimiter
            $delimiter = strpos($lines[0], ';') !== false ? ';' : ',';
            
            // Handle BOM
            if (strpos($lines[0], "\xEF\xBB\xBF") === 0) {
                $lines[0] = substr($lines[0], 3);
            }

            $data = array_map(fn($line) => str_getcsv($line, $delimiter), $lines);
            $header = array_map('trim', array_shift($data));
            $expectedColumns = ['name', 'code', 'description', 'is_elective', 'sort_order'];
            
            // Basic validation of header
            foreach ($expectedColumns as $col) {
                if (!in_array($col, $header)) {
                    return back()->with('error', "Missing required column: {$col}");
                }
            }

            $headerMap = array_flip($header);
            $successCount = 0;
            $errors = [];

            DB::beginTransaction();
            foreach ($data as $index => $row) {
                if (empty(array_filter($row))) continue;

                $getRowVal = function($key) use ($row, $headerMap) {
                    return isset($row[$headerMap[$key]]) ? trim($row[$headerMap[$key]]) : null;
                };

                $name = $getRowVal('name');
                $code = $getRowVal('code');
                
                if (empty($name) || empty($code)) {
                    $errors[] = "Row " . ($index + 2) . ": Name and Code are required.";
                    continue;
                }

                // Check for duplicate code
                if (Subject::where('code', $code)->exists()) {
                    $errors[] = "Row " . ($index + 2) . ": Subject code '{$code}' already exists.";
                    continue;
                }

                Subject::create([
                    'name' => $name,
                    'code' => $code,
                    'description' => $getRowVal('description'),
                    'is_elective' => filter_var($getRowVal('is_elective'), FILTER_VALIDATE_BOOLEAN),
                    'sort_order' => (int) $getRowVal('sort_order') ?: 0,
                    'is_active' => true,
                ]);

                $successCount++;
            }

            DB::commit();

            $message = "Import completed. {$successCount} subjects imported successfully.";
            if (!empty($errors)) {
                return redirect()->route('admin.subjects.index')
                    ->with('success', $message)
                    ->with('import_errors', $errors);
            }

            return redirect()->route('admin.subjects.index')->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }
}
