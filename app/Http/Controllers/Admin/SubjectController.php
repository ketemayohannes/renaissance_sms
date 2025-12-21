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
}
