<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GradeLevel;
use App\Models\Division;
use Illuminate\Http\Request;

class GradeLevelController extends Controller
{
    public function index()
    {
        $gradeLevels = GradeLevel::with('division')->orderBy('sort_order')->get();
        return view('admin.grade-levels.index', compact('gradeLevels'));
    }

    public function create()
    {
        $divisions = Division::where('is_active', true)->orderBy('sort_order')->get();
        return view('admin.grade-levels.create', compact('divisions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'division_id' => 'required|exists:divisions,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:grade_levels,code',
            'sort_order' => 'required|integer|min:0',
        ]);

        GradeLevel::create($request->all());

        return redirect()->route('admin.grade-levels.create')
            ->with('success', 'Grade Level created successfully.');
    }

    public function edit(GradeLevel $gradeLevel)
    {
        $divisions = Division::where('is_active', true)->orderBy('sort_order')->get();
        return view('admin.grade-levels.edit', compact('gradeLevel', 'divisions'));
    }

    public function update(Request $request, GradeLevel $gradeLevel)
    {
        $request->validate([
            'division_id' => 'required|exists:divisions,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:grade_levels,code,' . $gradeLevel->id,
            'sort_order' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $gradeLevel->update($request->all());

        return redirect()->route('admin.grade-levels.index')
            ->with('success', 'Grade Level updated successfully.');
    }

    public function destroy(GradeLevel $gradeLevel)
    {
        if ($gradeLevel->sections()->count() > 0) {
            return back()->with('error', 'Cannot delete grade level with existing sections.');
        }

        $gradeLevel->delete();

        return redirect()->route('admin.grade-levels.index')
            ->with('success', 'Grade Level deleted successfully.');
    }
}
