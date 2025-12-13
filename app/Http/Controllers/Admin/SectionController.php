<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Section;
use App\Models\GradeLevel;
use App\Models\AcademicYear;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SectionController extends Controller
{
    public function index()
    {
        $sections = Section::with(['gradeLevel.division', 'academicYear', 'homeroomTeacher'])
            ->orderBy('academic_year_id', 'desc')
            ->get();
        return view('admin.sections.index', compact('sections'));
    }

    public function create()
    {
        $gradeLevels = GradeLevel::with('division')->where('is_active', true)->orderBy('sort_order')->get();
        $academicYears = AcademicYear::orderBy('start_date', 'desc')->get();
        $teachers = User::role('Teacher')->get();
        
        return view('admin.sections.create', compact('gradeLevels', 'academicYears', 'teachers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'grade_level_id' => 'required|exists:grade_levels,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'name' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1',
            'homeroom_teacher_id' => 'nullable|exists:users,id',
        ]);

        Section::create($request->all());

        return redirect()->route('admin.sections.index')
            ->with('success', 'Section created successfully.');
    }

    public function edit(Section $section)
    {
        $gradeLevels = GradeLevel::with('division')->where('is_active', true)->orderBy('sort_order')->get();
        $academicYears = AcademicYear::orderBy('start_date', 'desc')->get();
        $teachers = User::role('Teacher')->get();
        
        return view('admin.sections.edit', compact('section', 'gradeLevels', 'academicYears', 'teachers'));
    }

    public function update(Request $request, Section $section)
    {
        $request->validate([
            'grade_level_id' => 'required|exists:grade_levels,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'name' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1',
            'homeroom_teacher_id' => 'nullable|exists:users,id',
            'is_active' => 'boolean',
        ]);

        $section->update($request->all());

        return redirect()->route('admin.sections.index')
            ->with('success', 'Section updated successfully.');
    }

    public function destroy(Section $section)
    {
        if ($section->students()->count() > 0) {
            return back()->with('error', 'Cannot delete section with enrolled students.');
        }

        $section->delete();

        return redirect()->route('admin.sections.index')
            ->with('success', 'Section deleted successfully.');
    }
    public function import()
    {
        return view('admin.sections.import');
    }

    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="sections_import_template.csv"',
        ];

        $columns = ['name', 'grade_level', 'division', 'capacity'];

        $callback = function() use ($columns) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for Excel compatibility
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Force Excel to use comma as separator
            fwrite($file, "sep=,\n");
            
            fputcsv($file, $columns);
            
            // Add sample row
            fputcsv($file, ['A', 'Grade 1', 'Elementary', '30']);
            fputcsv($file, ['B', 'Grade 1', 'Elementary', '30']);
            
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
        $required = ['name', 'grade_level', 'division'];
        foreach ($required as $col) {
            if (!isset($headerMap[$col])) {
                return back()->with('error', "Missing required column: $col. Found columns: " . implode(', ', $header));
            }
        }

        // Get active academic year
        $activeYear = AcademicYear::where('is_active', true)->first();
        if (!$activeYear) {
            return back()->with('error', 'No active academic year found. Please activate one first.');
        }

        DB::beginTransaction();
        try {
            $count = 0;
            foreach ($data as $row) {
                if (empty($row) || empty($row[0])) continue;

                $val = function($col) use ($row, $headerMap) {
                    return isset($headerMap[$col]) ? trim($row[$headerMap[$col]]) : null;
                };

                $divisionName = $val('division');
                $gradeLevelName = $val('grade_level');
                $sectionName = $val('name');
                $capacity = $val('capacity') ?? 30;

                // Find Grade Level
                $gradeLevel = GradeLevel::where('name', $gradeLevelName)
                    ->whereHas('division', function($q) use ($divisionName) {
                        $q->where('name', $divisionName);
                    })->first();

                if (!$gradeLevel) {
                    throw new \Exception("Grade Level '$gradeLevelName' in Division '$divisionName' not found.");
                }

                // Check if section exists
                $exists = Section::where('name', $sectionName)
                    ->where('grade_level_id', $gradeLevel->id)
                    ->where('academic_year_id', $activeYear->id)
                    ->exists();

                if ($exists) {
                    continue; // Skip existing
                }

                Section::create([
                    'name' => $sectionName,
                    'grade_level_id' => $gradeLevel->id,
                    'academic_year_id' => $activeYear->id,
                    'capacity' => $capacity,
                    'is_active' => true,
                ]);

                $count++;
            }
            
            DB::commit();
            return redirect()->route('admin.sections.index')->with('success', "$count sections imported successfully.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }
}
