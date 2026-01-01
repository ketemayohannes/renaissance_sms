<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Section;
use App\Models\Subject;
use Illuminate\Http\Request;

class GlobalSearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->get('q');
        
        if (!$query || strlen($query) < 2) {
            return response()->json([]);
        }

        $results = collect();

        // Search Students
        $students = Student::search($query)
            ->limit(5)
            ->get()
            ->map(function ($student) {
                return [
                    'title' => $student->full_name,
                    'subtitle' => "ID: {$student->student_id}",
                    'url' => route('admin.students.show', $student),
                    'type' => 'Student',
                    'icon' => 'user'
                ];
            });
        $results = $results->merge($students);

        // Search Sections
        $sections = Section::where('name', 'like', "%{$query}%")
            ->limit(3)
            ->get()
            ->map(function ($section) {
                return [
                    'title' => "Section {$section->name}",
                    'subtitle' => $section->gradeLevel->name ?? 'N/A',
                    'url' => route('admin.sections.edit', $section),
                    'type' => 'Section',
                    'icon' => 'collection'
                ];
            });
        $results = $results->merge($sections);

        // Search Subjects
        $subjects = Subject::where('name', 'like', "%{$query}%")
            ->orWhere('code', 'like', "%{$query}%")
            ->limit(3)
            ->get()
            ->map(function ($subject) {
                return [
                    'title' => $subject->name,
                    'subtitle' => $subject->code,
                    'url' => route('admin.subjects.edit', $subject),
                    'type' => 'Subject',
                    'icon' => 'book-open'
                ];
            });
        $results = $results->merge($subjects);

        return response()->json($results);
    }
}
