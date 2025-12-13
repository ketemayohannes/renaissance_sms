<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\AssessmentType;
use App\Models\GradeComponent;
use App\Models\GradeLevel;
use App\Models\Subject;
use App\Models\Term;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GradeComponentController extends Controller
{
    public function index(Request $request)
    {
        $query = GradeComponent::with(['academicYear', 'gradeLevel', 'subject', 'term', 'assessmentType']);

        if ($request->academic_year_id) {
            $query->where('academic_year_id', $request->academic_year_id);
        }
        if ($request->grade_level_id) {
            $query->where('grade_level_id', $request->grade_level_id);
        }
        if ($request->subject_id) {
            $query->where('subject_id', $request->subject_id);
        }
        if ($request->term_id) {
            $query->where('term_id', $request->term_id);
        }

        $components = $query->orderBy('academic_year_id', 'desc')
            ->orderBy('term_id')
            ->orderBy('name')
            ->orderBy('order')
            ->get();

        // Group components by academic_year_id, term_id, and name
        $groupedComponents = $components->groupBy(function($component) {
            return $component->academic_year_id . '_' . 
                   ($component->term_id ?? 'all') . '_' . 
                   $component->name . '_' . 
                   $component->weight . '_' . 
                   $component->assessment_type_id;
        })->map(function($group) {
            $first = $group->first();
            return [
                'id' => $first->id,
                'component_ids' => $group->pluck('id')->toArray(),
                'name' => $first->name,
                'academic_year' => $first->academicYear,
                'academic_year_id' => $first->academic_year_id,
                'term' => $first->term,
                'term_id' => $first->term_id,
                'assessment_type' => $first->assessmentType,
                'weight' => $first->weight,
                'max_score' => $first->max_score,
                'order' => $first->order,
                'is_active' => $first->is_active,
                'grade_levels' => $group->pluck('gradeLevel')->unique('id')->sortBy('name'),
                'subjects' => $group->pluck('subject')->unique('id')->sortBy('name'),
                'count' => $group->count(),
            ];
        })->values();

        // Calculate weight totals for each year/term/grade/subject combination
        // Only include combinations where the subject is actually assigned to the grade level
        $weightTotals = $components->groupBy(function($component) {
            return $component->academic_year_id . '_' . 
                   ($component->term_id ?? 'all') . '_' . 
                   $component->grade_level_id . '_' . 
                   $component->subject_id;
        })->map(function($group) {
            $first = $group->first();
            
            // Check if this subject is assigned to this grade level
            $isAssigned = \DB::table('grade_level_subjects')
                ->where('grade_level_id', $first->grade_level_id)
                ->where('subject_id', $first->subject_id)
                ->exists();
            
            // Only return if the subject is assigned to the grade level
            if (!$isAssigned) {
                return null;
            }
            
            return [
                'total' => $group->sum('weight'),
                'academic_year_id' => $first->academic_year_id,
                'term_id' => $first->term_id,
                'grade_level_id' => $first->grade_level_id,
                'subject_id' => $first->subject_id,
                'academic_year' => $first->academicYear->name,
                'term' => $first->term->name ?? 'All Terms',
                'grade_level' => $first->gradeLevel->name,
                'subject' => $first->subject->name,
            ];
        })->filter(function($item) {
            return $item !== null && $item['total'] != 100; // Only show incomplete ones with valid assignments
        })->values();

        $academicYears = AcademicYear::latest()->get();
        $gradeLevels = GradeLevel::all();
        $subjects = Subject::all();
        $terms = Term::all();

        return view('admin.grade-components.index', compact('groupedComponents', 'weightTotals', 'academicYears', 'gradeLevels', 'subjects', 'terms'));
    }

    public function create()
    {
        $academicYears = AcademicYear::where('is_active', true)->get();
        $gradeLevels = GradeLevel::all();
        $subjects = Subject::where('is_active', true)->get();
        $assessmentTypes = AssessmentType::where('is_active', true)->get();
        
        return view('admin.grade-components.create', compact('academicYears', 'gradeLevels', 'subjects', 'assessmentTypes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'grade_level_ids' => 'required|array',
            'grade_level_ids.*' => 'exists:grade_levels,id',
            'subject_ids' => 'required|array',
            'subject_ids.*' => 'exists:subjects,id',
            'term_id' => 'nullable|exists:terms,id',
            'assessment_type_id' => 'required|exists:assessment_types,id',
            'name' => 'required|string|max:100',
            'weight' => 'required|numeric|min:0|max:100',
            'order' => 'integer',
        ]);

        $created = 0;
        $errors = [];

        // Create component for each grade level and subject combination
        foreach ($request->grade_level_ids as $gradeLevelId) {
            foreach ($request->subject_ids as $subjectId) {
                // Check if component already exists
                $exists = GradeComponent::where('academic_year_id', $request->academic_year_id)
                    ->where('grade_level_id', $gradeLevelId)
                    ->where('subject_id', $subjectId)
                    ->where('term_id', $request->term_id)
                    ->where('name', $request->name)
                    ->exists();

                if ($exists) {
                    $errors[] = "Component '{$request->name}' already exists for this combination";
                    continue;
                }

                // Check total weight
                $currentWeight = GradeComponent::where('academic_year_id', $request->academic_year_id)
                    ->where('grade_level_id', $gradeLevelId)
                    ->where('subject_id', $subjectId)
                    ->where('term_id', $request->term_id)
                    ->sum('weight');

                if (($currentWeight + $request->weight) > 100) {
                    $gradeLevel = \App\Models\GradeLevel::find($gradeLevelId);
                    $subject = \App\Models\Subject::find($subjectId);
                    $remaining = 100 - $currentWeight;
                    $errors[] = "{$gradeLevel->name} - {$subject->name}: Cannot add {$request->weight}%. Current total: {$currentWeight}%, Remaining: {$remaining}%";
                    continue;
                }

                $data = $request->except(['grade_level_ids', 'subject_ids']);
                $data['grade_level_id'] = $gradeLevelId;
                $data['subject_id'] = $subjectId;
                $data['max_score'] = $request->weight; // Enforce max_score = weight

                GradeComponent::create($data);
                $created++;
            }
        }

        if ($created > 0) {
            $message = "Successfully created {$created} grade component(s).";
            if (count($errors) > 0) {
                $message .= " Some components were skipped: " . implode(', ', array_unique($errors));
            }
            return back()->with('success', $message);
        }

        return back()->withInput()->withErrors(['error' => 'No components were created. ' . implode(', ', $errors)]);
    }

    public function edit(GradeComponent $gradeComponent)
    {
        $academicYears = AcademicYear::all();
        $gradeLevels = GradeLevel::all();
        $subjects = Subject::all();
        $assessmentTypes = AssessmentType::all();
        $terms = Term::where('academic_year_id', $gradeComponent->academic_year_id)->get();

        return view('admin.grade-components.edit', compact('gradeComponent', 'academicYears', 'gradeLevels', 'subjects', 'assessmentTypes', 'terms'));
    }

    public function update(Request $request, GradeComponent $gradeComponent)
    {
        $validationRules = [
            'academic_year_id' => 'required|exists:academic_years,id',
            'grade_level_id' => 'required|exists:grade_levels,id',
            'subject_id' => 'required|exists:subjects,id',
            'term_id' => 'nullable|exists:terms,id',
            'assessment_type_id' => 'required|exists:assessment_types,id',
            'name' => 'required|string|max:100',
            'weight' => 'required|numeric|min:0|max:100',
            'max_score' => 'required|numeric|min:1',
            'order' => 'integer',
        ];

        // Only check name uniqueness if the name is being changed
        if ($request->name !== $gradeComponent->name) {
            $validationRules['name'] = [
                'required',
                'string',
                'max:100',
                Rule::unique('grade_components')->where(function ($query) use ($request) {
                    return $query->where('academic_year_id', $request->academic_year_id)
                        ->where('grade_level_id', $request->grade_level_id)
                        ->where('subject_id', $request->subject_id)
                        ->where('term_id', $request->term_id);
                })->ignore($gradeComponent->id),
            ];
        }

        $request->validate($validationRules);

        // Check total weight only if weight is being changed
        if ($request->weight != $gradeComponent->weight) {
            $currentWeight = GradeComponent::where('academic_year_id', $request->academic_year_id)
                ->where('grade_level_id', $request->grade_level_id)
                ->where('subject_id', $request->subject_id)
                ->where('term_id', $request->term_id)
                ->where('id', '!=', $gradeComponent->id)
                ->sum('weight');

            if (($currentWeight + $request->weight) > 100) {
                return back()->withInput()->withErrors(['weight' => 'Total weight cannot exceed 100%. Current total (excluding this): ' . $currentWeight . '%']);
            }
        }

        $data = $request->all();
        $data['max_score'] = $request->weight; // Enforce max_score = weight

        $gradeComponent->update($data);

        return back()->with('success', 'Grade Component updated successfully.');
    }

    public function destroy(GradeComponent $gradeComponent)
    {
        $gradeComponent->delete();

        return redirect()->route('admin.grade-components.index')
            ->with('success', 'Grade Component deleted successfully.');
    }

    public function getComponents(Request $request)
    {
        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'grade_level_id' => 'required|exists:grade_levels,id',
            'subject_id' => 'required|exists:subjects,id',
            'term_id' => 'nullable|exists:terms,id',
        ]);

        $components = GradeComponent::where('academic_year_id', $request->academic_year_id)
            ->where('grade_level_id', $request->grade_level_id)
            ->where('subject_id', $request->subject_id)
            ->where('term_id', $request->term_id)
            ->where('is_active', true)
            ->orderBy('order')
            ->get();

        return response()->json($components);
    }
}
