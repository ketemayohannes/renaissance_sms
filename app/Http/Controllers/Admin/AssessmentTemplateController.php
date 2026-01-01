<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AssessmentTemplate;
use App\Models\AssessmentTemplateAssignment;
use App\Models\AcademicYear;
use App\Models\GradeLevel;
use App\Models\Subject;
use App\Models\AssessmentType;
use App\Models\Term;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssessmentTemplateController extends Controller
{
    public function index(Request $request)
    {
        $query = AssessmentTemplate::with(['academicYear', 'term', 'assessmentType', 'assignments.gradeLevel', 'assignments.subject']);

        // Exclude TERM_TOTAL - it's auto-created by master sheet and is not a real assessment template
        $query->whereHas('assessmentType', function($q) {
            $q->where('code', '!=', 'TERM_TOTAL');
        });

        // Apply filters
        if ($request->academic_year_id) {
            $query->where('academic_year_id', $request->academic_year_id);
        }
        if ($request->term_id) {
            $query->where('term_id', $request->term_id);
        }

        $templates = $query->orderBy('academic_year_id', 'desc')
            ->orderBy('term_id')
            ->orderBy('order')
            ->get();

        // Group templates by settings (excluding term)
        $groupedTemplates = $templates->groupBy(function($t) {
            return $t->academic_year_id . '-' . $t->assessment_type_id . '-' . $t->name . '-' . $t->weight . '-' . $t->max_score . '-' . $t->order;
        })->map(function ($group) {
            $first = $group->first();
            return [
                'ids' => $group->pluck('id'), // Multiple IDs for the group (one per term)
                'academic_year' => $first->academicYear,
                'name' => $first->name,
                'weight' => $first->weight,
                'max_score' => $first->max_score,
                'order' => $first->order,
                'is_active' => $first->is_active,
                'assessment_type' => $first->assessmentType,
                'terms' => $group->map(fn($t) => [
                    'id' => $t->id,
                    'name' => $t->term ? $t->term->name : 'All Terms',
                    'term_number' => $t->term ? $t->term->term_number : 0
                ])->sortBy('term_number')->values(),
                'grade_levels' => $group->flatMap(fn($t) => $t->assignments->pluck('gradeLevel'))->unique('id')->sortBy('name'),
                'subjects' => $group->flatMap(fn($t) => $t->assignments->pluck('subject'))->unique('id')->sortBy('name'),
                'assignment_count' => $group->sum(fn($t) => $t->assignments->count()),
            ];
        });

        // Calculate weight totals and warnings
        $weightWarnings = $this->calculateWeightWarnings($templates);

        // PERFORMANCE: Use cached data
        $academicYears = \App\Helpers\CachedData::academicYears();
        $terms = \App\Helpers\CachedData::terms();

        return view('admin.assessment-templates.index', compact('groupedTemplates', 'weightWarnings', 'academicYears', 'terms'));
    }

    public function create()
    {
        $academicYears = AcademicYear::where('is_active', true)->get();
        $gradeLevels = GradeLevel::all();
        $subjects = Subject::where('is_active', true)->get();
        $assessmentTypes = AssessmentType::where('is_active', true)->get();

        return view('admin.assessment-templates.create', compact('academicYears', 'gradeLevels', 'subjects', 'assessmentTypes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'term_ids' => 'nullable|array',
            'term_ids.*' => 'nullable|distinct', // Can be null or numeric ID
            'assessment_type_id' => 'required|exists:assessment_types,id',
            'name' => 'required|string|max:100',
            'weight' => 'required|numeric|min:0|max:100',
            'order' => 'integer',
            'grade_level_ids' => 'required|array|min:1',
            'grade_level_ids.*' => 'exists:grade_levels,id',
            'subject_ids' => 'required|array|min:1',
            'subject_ids.*' => 'exists:subjects,id',
        ]);

        DB::beginTransaction();
        try {
            // Prepare Terms to loop over. If empty or contains null, we treat as one iteration with null.
            // But wait, if user selects "All Terms" (value ""), term_ids array will contain null/empty string.
            // If they select multiple quarters, it will contain IDs.
            // We should filter and normalize.
            
            $termIds = $request->term_ids ?? [null];
            
            if (empty($termIds)) {
                $termIds = [null];
            }

            $totalCreated = 0;
            $allErrors = [];

            foreach ($termIds as $termId) {
                // Determine if this is a "Global" template (null term) or specific
                $finalTermId = $termId ? $termId : null;

                // Create the template
                $template = AssessmentTemplate::create([
                    'academic_year_id' => $request->academic_year_id,
                    'term_id' => $finalTermId,
                    'assessment_type_id' => $request->assessment_type_id,
                    'name' => $request->name,
                    'weight' => $request->weight,
                    'max_score' => $request->weight, // Enforce max_score = weight
                    'order' => $request->order ?? 0,
                    'is_active' => true,
                ]);

                // Create assignments for all grade/subject combinations
                foreach ($request->grade_level_ids as $gradeLevelId) {
                    foreach ($request->subject_ids as $subjectId) {
                        // Check if subject is assigned to grade level
                        $isAssigned = DB::table('grade_level_subjects')
                            ->where('grade_level_id', $gradeLevelId)
                            ->where('subject_id', $subjectId)
                            ->exists();

                        if (!$isAssigned) {
                            continue; // Skip invalid combinations
                        }

                        // Check weight total
                        $currentWeight = $this->getTotalWeight($request->academic_year_id, $finalTermId, $gradeLevelId, $subjectId);

                        if (($currentWeight + $request->weight) > 100) {
                            $gradeLevel = GradeLevel::find($gradeLevelId);
                            $subject = Subject::find($subjectId);
                            $remaining = 100 - $currentWeight;
                            $termName = $finalTermId ? Term::find($finalTermId)->name : 'All Terms';
                            $allErrors[] = "[{$termName}] {$gradeLevel->name} - {$subject->name}: Cannot add {$request->weight}%. Current: {$currentWeight}%, Remaining: {$remaining}%";
                            continue;
                        }

                        // Create assignment
                        AssessmentTemplateAssignment::create([
                            'assessment_template_id' => $template->id,
                            'grade_level_id' => $gradeLevelId,
                            'subject_id' => $subjectId,
                        ]);

                        $totalCreated++;
                    }
                }
            }

            DB::commit();

            $message = "Successfully created assessment templates with {$totalCreated} assignment(s).";
            if (count($allErrors) > 0) {
                $message .= " Some combinations were skipped: " . implode(' | ', array_unique($allErrors));
            }

            return back()->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Error creating template: ' . $e->getMessage()]);
        }
    }

    public function edit(AssessmentTemplate $assessmentTemplate)
    {
        $assessmentTemplate->load(['assignments.gradeLevel', 'assignments.subject']);
        
        $academicYears = AcademicYear::all();
        $gradeLevels = GradeLevel::all();
        $subjects = Subject::all();
        $assessmentTypes = AssessmentType::all();
        $terms = Term::where('academic_year_id', $assessmentTemplate->academic_year_id)->get();

        // Get currently assigned grade levels and subjects
        $assignedGradeLevels = $assessmentTemplate->assignments->pluck('grade_level_id')->unique()->toArray();
        $assignedSubjects = $assessmentTemplate->assignments->pluck('subject_id')->unique()->toArray();

        return view('admin.assessment-templates.edit', compact(
            'assessmentTemplate',
            'academicYears',
            'gradeLevels',
            'subjects',
            'assessmentTypes',
            'terms',
            'assignedGradeLevels',
            'assignedSubjects'
        ));
    }

    public function update(Request $request, AssessmentTemplate $assessmentTemplate)
    {
        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'term_ids' => 'nullable|array',
            'term_ids.*' => 'nullable|distinct',
            'assessment_type_id' => 'required|exists:assessment_types,id',
            'name' => 'required|string|max:100',
            'weight' => 'required|numeric|min:0|max:100',
            'order' => 'integer',
            'grade_level_ids' => 'required|array|min:1',
            'grade_level_ids.*' => 'exists:grade_levels,id',
            'subject_ids' => 'required|array|min:1',
            'subject_ids.*' => 'exists:subjects,id',
        ]);

        DB::beginTransaction();
        try {
            // Logic for Update:
            // 1. Identify valid selected term IDs.
            // 2. Use the FIRST valid term ID to update the CURRENT template (preserves ID/Refs).
            // 3. For any OTHER valid term IDs, create NEW templates (Clones).
            
            $termIds = $request->term_ids ?? [null];
            if (empty($termIds)) { $termIds = [null]; }

            // Take the first term to be the "owner" of this existing template
            $primaryTermId = array_shift($termIds);
            $finalPrimaryTermId = $primaryTermId ? $primaryTermId : null;

            // Update the existing template
            $assessmentTemplate->update([
                'academic_year_id' => $request->academic_year_id,
                'term_id' => $finalPrimaryTermId,
                'assessment_type_id' => $request->assessment_type_id,
                'name' => $request->name,
                'weight' => $request->weight,
                'max_score' => $request->weight,
                'order' => $request->order ?? 0,
            ]);

            // Re-assign Grade/Subjects for the primary template
            $assessmentTemplate->assignments()->delete();
            
            $errors = [];
            $createdCount = 0;

            // Helper to process assignments for a template
            $processAssignments = function($template, $gradeIds, $subjectIds, $yearId, $termId) use (&$errors, $request) {
                $count = 0;
                foreach ($gradeIds as $gradeLevelId) {
                    foreach ($subjectIds as $subjectId) {
                        $isAssigned = DB::table('grade_level_subjects')->where('grade_level_id', $gradeLevelId)->where('subject_id', $subjectId)->exists();
                        if (!$isAssigned) continue;

                        $currentWeight = $this->getTotalWeight($yearId, $termId, $gradeLevelId, $subjectId, $template->id);
                        
                        if (($currentWeight + $request->weight) > 100) {
                            $gradeLevel = GradeLevel::find($gradeLevelId);
                            $subject = Subject::find($subjectId);
                            $remaining = 100 - $currentWeight;
                             $termName = $termId ? Term::find($termId)->name : 'All Terms';
                            $errors[] = "[{$termName}] {$gradeLevel->name} - {$subject->name}: Cannot add {$request->weight}%. Current: {$currentWeight}%, Remaining: {$remaining}%";
                            continue;
                        }

                        AssessmentTemplateAssignment::create([
                            'assessment_template_id' => $template->id,
                            'grade_level_id' => $gradeLevelId,
                            'subject_id' => $subjectId,
                        ]);
                        $count++;
                    }
                }
                return $count;
            };

            // Process Primary
            $createdCount += $processAssignments($assessmentTemplate, $request->grade_level_ids, $request->subject_ids, $request->academic_year_id, $finalPrimaryTermId);

            // Process Clones (Remaining Term IDs)
            foreach ($termIds as $cloneTermId) {
                $finalCloneTermId = $cloneTermId ? $cloneTermId : null;
                
                // Create New Template
                $newTemplate = AssessmentTemplate::create([
                    'academic_year_id' => $request->academic_year_id,
                    'term_id' => $finalCloneTermId,
                    'assessment_type_id' => $request->assessment_type_id,
                    'name' => $request->name,
                    'weight' => $request->weight,
                    'max_score' => $request->weight,
                    'order' => $request->order ?? 0,
                    'is_active' => true,
                ]);

                $createdCount += $processAssignments($newTemplate, $request->grade_level_ids, $request->subject_ids, $request->academic_year_id, $finalCloneTermId);
            }

            DB::commit();

            $message = "Successfully updated assessment template. Total assignments active: {$createdCount}.";
            if (count($termIds) > 0) {
                 $message .= " Created new templates for " . count($termIds) . " additional terms.";
            }
            if (count($errors) > 0) {
                $message .= " Some combinations were skipped: " . implode(' | ', array_unique($errors));
            }

            return back()->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Error updating template: ' . $e->getMessage()]);
        }
    }

    public function destroy(AssessmentTemplate $assessmentTemplate)
    {
        try {
            $assessmentTemplate->delete(); // Cascade will delete assignments
            return redirect()->route('admin.assessment-templates.index')
                ->with('success', 'Assessment template deleted successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error deleting template: ' . $e->getMessage()]);
        }
    }

    // Helper method to calculate total weight for a grade/subject/term combination
    private function getTotalWeight($academicYearId, $termId, $gradeLevelId, $subjectId, $excludeTemplateId = null)
    {
        $query = AssessmentTemplate::where('academic_year_id', $academicYearId)
            ->where('term_id', $termId)
            ->whereHas('assessmentType', function($q) {
                $q->where('code', '!=', 'TERM_TOTAL');
            })
            ->whereHas('assignments', function ($q) use ($gradeLevelId, $subjectId) {
                $q->where('grade_level_id', $gradeLevelId)
                  ->where('subject_id', $subjectId);
            });

        if ($excludeTemplateId) {
            $query->where('id', '!=', $excludeTemplateId);
        }

        return $query->sum('weight');
    }

    // Helper method to calculate weight warnings
    private function calculateWeightWarnings($templates)
    {
        $warnings = collect();

        // Group by academic_year, term, grade, subject
        // Exclude TERM_TOTAL from validation as it's a parallel grading mechanism
        $grouped = $templates->filter(function($t) {
            return $t->assessmentType && $t->assessmentType->code !== 'TERM_TOTAL';
        })->flatMap(function ($template) {
            return $template->assignments->map(function ($assignment) use ($template) {
                return [
                    'academic_year_id' => $template->academic_year_id,
                    'term_id' => $template->term_id,
                    'grade_level_id' => $assignment->grade_level_id,
                    'subject_id' => $assignment->subject_id,
                    'weight' => $template->weight,
                    'academic_year' => $template->academicYear->name,
                    'term' => $template->term->name ?? 'All Terms',
                    'grade_level' => $assignment->gradeLevel->name,
                    'subject' => $assignment->subject->name,
                ];
            });
        })->groupBy(function ($item) {
            return $item['academic_year_id'] . '_' . ($item['term_id'] ?? 'all') . '_' . $item['grade_level_id'] . '_' . $item['subject_id'];
        });

        foreach ($grouped as $key => $group) {
            $total = $group->sum('weight');
            if ($total != 100) {
                $first = $group->first();
                $warnings->push([
                    'total' => $total,
                    'academic_year' => $first['academic_year'],
                    'term' => $first['term'],
                    'grade_level' => $first['grade_level'],
                    'subject' => $first['subject'],
                ]);
            }
        }

        return $warnings;
    }
}
