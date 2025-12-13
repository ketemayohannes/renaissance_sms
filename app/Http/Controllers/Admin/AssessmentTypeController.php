<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AssessmentType;
use Illuminate\Http\Request;

class AssessmentTypeController extends Controller
{
    public function index()
    {
        $assessmentTypes = AssessmentType::latest()->get();
        return view('admin.assessment-types.index', compact('assessmentTypes'));
    }

    public function create()
    {
        return view('admin.assessment-types.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:20|unique:assessment_types,code',
            'description' => 'nullable|string',
        ]);

        AssessmentType::create($request->all());

        return redirect()->route('admin.assessment-types.index')
            ->with('success', 'Assessment Type created successfully.');
    }

    public function edit(AssessmentType $assessmentType)
    {
        return view('admin.assessment-types.edit', compact('assessmentType'));
    }

    public function update(Request $request, AssessmentType $assessmentType)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:20|unique:assessment_types,code,' . $assessmentType->id,
            'description' => 'nullable|string',
        ]);

        $assessmentType->update($request->all());

        return redirect()->route('admin.assessment-types.index')
            ->with('success', 'Assessment Type updated successfully.');
    }

    public function destroy(AssessmentType $assessmentType)
    {
        $assessmentType->delete();

        return redirect()->route('admin.assessment-types.index')
            ->with('success', 'Assessment Type deleted successfully.');
    }
}
