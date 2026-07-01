<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PromotionRule;
use App\Models\StudentPromotion;
use App\Models\GradeLevel;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\StudentTermRecord;
use App\Models\AcademicYear;
use App\Models\Term;
use App\Models\Division;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PromotionController extends Controller
{
    public function index()
    {
        $academicYear = AcademicYear::where('is_active', true)->first();
        $promotionRules = PromotionRule::with(['fromGradeLevel', 'toGradeLevel'])
            ->where('academic_year_id', $academicYear->id)
            ->get();
        $gradeLevels = GradeLevel::with('subjects')->orderBy('sort_order')->get();
        
        return view('admin.promotions.index', compact('promotionRules', 'gradeLevels', 'academicYear'));
    }

    public function storeRule(Request $request)
    {
        $request->validate([
            'from_grade_level_id' => 'required|exists:grade_levels,id',
            'to_grade_level_id' => 'nullable|exists:grade_levels,id',
            'min_average' => 'required|numeric|min:0|max:100',
            'min_attendance_percent' => 'nullable|numeric|min:0|max:100',
            'max_failed_subjects' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
            'major_subjects' => 'nullable|array',
            'conditional_rules' => 'nullable|array',
            'failed_action' => 'required|string|in:retained,re_exam',
        ]);

        $academicYear = AcademicYear::where('is_active', true)->first();

        $conditionalRules = [];
        if ($request->has('conditional_rules') && is_array($request->conditional_rules)) {
            foreach ($request->conditional_rules as $rule) {
                if (isset($rule['fails']) && isset($rule['avg'])) {
                    $conditionalRules[] = [
                        'fails' => (int) $rule['fails'],
                        'avg' => (float) $rule['avg'],
                    ];
                }
            }
        }

        PromotionRule::updateOrCreate(
            [
                'from_grade_level_id' => $request->from_grade_level_id,
                'academic_year_id' => $academicYear->id,
            ],
            [
                'to_grade_level_id' => $request->to_grade_level_id,
                'min_average' => $request->min_average,
                'min_attendance_percent' => $request->min_attendance_percent ?? 75,
                'max_failed_subjects' => $request->max_failed_subjects ?? 0,
                'description' => $request->description,
                'major_subjects' => $request->major_subjects ?? [],
                'conditional_rules' => $conditionalRules,
                'failed_action' => $request->failed_action ?? 'retained',
            ]
        );

        return redirect()->route('admin.promotions.index')
            ->with('success', 'Promotion rule saved successfully!');
    }

    public function deleteRule(PromotionRule $promotionRule)
    {
        $promotionRule->delete();
        return redirect()->route('admin.promotions.index')
            ->with('success', 'Promotion rule deleted hideously!');
    }

    public function processForm()
    {
        $academicYear = AcademicYear::where('is_active', true)->first();
        $nextAcademicYear = AcademicYear::where('start_date', '>', $academicYear->end_date)
            ->orderBy('start_date')
            ->first();
        
        $divisions = Division::with(['gradeLevels' => function($q) {
            $q->orderBy('sort_order');
        }, 'gradeLevels.sections' => function($q) use ($academicYear) {
            $q->where('academic_year_id', $academicYear->id)->where('is_active', true);
        }])->orderBy('sort_order')->get();

        return view('admin.promotions.process', compact('academicYear', 'nextAcademicYear', 'divisions'));
    }

    public function preview(Request $request)
    {
        $request->validate([
            'section_id' => 'required|exists:sections,id',
        ]);

        $section = Section::with('gradeLevel.subjects')->findOrFail($request->section_id);
        $academicYear = AcademicYear::where('is_active', true)->first();
        
        $yearlyTerm = Term::where('academic_year_id', $academicYear->id)
            ->where('type', 'yearly')
            ->first();

        if (!$yearlyTerm) {
            // Virtual yearly term for calculation if not in DB
            $yearlyTerm = new Term(['type' => 'yearly', 'name' => 'Yearly', 'academic_year_id' => $academicYear->id]);
            $yearlyTerm->incrementing = false;
            $yearlyTerm->id = 'yearly';
        }

        $students = Student::whereHas('enrollments', function($q) use ($section, $academicYear) {
            $q->where('section_id', $section->id)
              ->where('academic_year_id', $academicYear->id)
              ->where('status', 'active');
        })->get();

        $promotionRule = PromotionRule::where('from_grade_level_id', $section->grade_level_id)
            ->where('academic_year_id', $academicYear->id)
            ->first();

        // Use GradingService to get accurate yearly scores and counts
        $gradingService = app(\App\Services\GradingService::class);
        $subjects = $section->gradeLevel->subjects;
        $batchResults = $gradingService->calculateSectionTotals($students, $yearlyTerm, $academicYear, $subjects);

        $passMark = 50; 
        $previewData = [];
        $subjectsMap = $subjects->keyBy('id');

        foreach ($students as $student) {
            $res = $batchResults[$student->id] ?? ['total' => 0, 'average' => 0, 'marks' => []];
            $average = $res['average'];
            
            $failedMajorCount = 0;
            $failedNonMajorCount = 0;
            $failedSubjects = 0;
            $failedSubjectNames = [];

            $majorSubjectIds = $promotionRule ? ($promotionRule->major_subjects ?? []) : [];
            $majorSubjectIds = array_map('strval', $majorSubjectIds);

            foreach ($res['marks'] as $subId => $mark) {
                if ($mark < $passMark) {
                    $failedSubjects++;
                    $subjectName = $subjectsMap->get($subId)->name ?? 'Unknown';
                    $failedSubjectNames[] = $subjectName;

                    if (in_array((string)$subId, $majorSubjectIds)) {
                        $failedMajorCount++;
                    } else {
                        $failedNonMajorCount++;
                    }
                }
            }

            $recommended = 'retained';
            $passesAverage = false;

            if (!$promotionRule) {
                $passesAverage = ($average >= 50);
                $recommended = ($passesAverage && $failedSubjects === 0) ? 'promoted' : 'retained';
            } else {
                if (empty($promotionRule->to_grade_level_id)) {
                    $recommended = 'graduated';
                    $passesAverage = true;
                } else {
                    $failedAction = $promotionRule->failed_action ?? 'retained';

                    if ($failedSubjects === 0) {
                        $passesAverage = ($average >= $promotionRule->min_average);
                        $recommended = $passesAverage ? 'promoted' : $failedAction;
                    } else {
                        if ($failedMajorCount > 0) {
                            $recommended = $failedAction;
                        } else {
                            $matchedRule = null;
                            $conditionalRules = $promotionRule->conditional_rules ?? [];
                            foreach ($conditionalRules as $cRule) {
                                if (isset($cRule['fails']) && (int)$cRule['fails'] === $failedNonMajorCount) {
                                    $matchedRule = $cRule;
                                    break;
                                }
                            }

                            if ($matchedRule) {
                                $passesAverage = ($average >= (float)$matchedRule['avg']);
                                $recommended = $passesAverage ? 'promoted' : $failedAction;
                            } else {
                                $recommended = $failedAction;
                            }
                        }
                    }
                }
            }

            $previewData[] = [
                'student' => $student,
                'average' => $average,
                'passesAverage' => $passesAverage,
                'failedSubjects' => $failedSubjects,
                'failedMajorCount' => $failedMajorCount,
                'failedNonMajorCount' => $failedNonMajorCount,
                'failedSubjectNames' => $failedSubjectNames,
                'recommended' => $recommended,
            ];
        }

        $nextAcademicYear = AcademicYear::where('start_date', '>', $academicYear->end_date)
            ->orderBy('start_date')
            ->first();
        // For streamed grades (e.g. Grade 11 (Natural)), promote to the same-stream next grade.
        // For non-streamed grades, pick the next grade by sort_order.
        $currentGradeName = $section->gradeLevel->name;
        preg_match('/\(([^)]+)\)/', $currentGradeName, $streamMatch);
        $stream = $streamMatch[1] ?? null; // e.g. 'Natural', 'Social', or null

        if ($stream) {
            // Same-stream, next division grade: find next grade that also contains the same stream label
            $nextGradeLevel = GradeLevel::where('sort_order', '>', $section->gradeLevel->sort_order)
                ->where('name', 'like', "%({$stream})%")
                ->orderBy('sort_order')
                ->first();
        } else {
            // Non-streamed: skip over any streamed grades by picking next non-streamed grade in same division,
            // or simply the very next sort_order (e.g. Grade 10 → Grade 11 Natural, handled at enrollment)
            $nextGradeLevel = GradeLevel::where('sort_order', '>', $section->gradeLevel->sort_order)
                ->where('division_id', $section->gradeLevel->division_id)
                ->where(function($q) {
                    $q->where('name', 'not like', '%(Social)%');
                })
                ->orderBy('sort_order')
                ->first();
        }

        return view('admin.promotions.preview', compact(
            'section', 'previewData', 'promotionRule', 'academicYear', 'nextAcademicYear', 'nextGradeLevel'
        ));
    }

    public function execute(Request $request)
    {
        $request->validate([
            'section_id' => 'required|exists:sections,id',
            'decisions' => 'required|array',
            'next_academic_year_id' => 'required|exists:academic_years,id',
        ]);

        $section = Section::with('gradeLevel')->findOrFail($request->section_id);
        $academicYear = AcademicYear::where('is_active', true)->first();
        $nextAcademicYear = AcademicYear::findOrFail($request->next_academic_year_id);
        // For streamed grades (e.g. Grade 11 (Natural)), promote to the same-stream next grade.
        preg_match('/\(([^)]+)\)/', $section->gradeLevel->name, $streamMatch);
        $stream = $streamMatch[1] ?? null;

        if ($stream) {
            $nextGradeLevel = GradeLevel::where('sort_order', '>', $section->gradeLevel->sort_order)
                ->where('name', 'like', "%({$stream})%")
                ->orderBy('sort_order')
                ->first();
        } else {
            $nextGradeLevel = GradeLevel::where('sort_order', '>', $section->gradeLevel->sort_order)
                ->where('division_id', $section->gradeLevel->division_id)
                ->where(function($q) {
                    $q->where('name', 'not like', '%(Social)%');
                })
                ->orderBy('sort_order')
                ->first();
        }

        $promotedCount = 0;
        $retainedCount = 0;
        $graduatedCount = 0;
        $reExamCount = 0;

        DB::transaction(function() use ($request, $section, $academicYear, $nextAcademicYear, $nextGradeLevel, &$promotedCount, &$retainedCount, &$graduatedCount, &$reExamCount) {
            foreach ($request->decisions as $studentId => $decision) {
                
                $toGradeLevelId = null;
                $toSectionId = null;

                if ($decision === 'promoted') {
                    if ($nextGradeLevel) {
                        $toGradeLevelId = $nextGradeLevel->id;
                        // Try to find a section with the same name in the next grade level (e.g. 1A -> 2A)
                        $targetSection = Section::where('grade_level_id', $nextGradeLevel->id)
                            ->where('name', $section->name)
                            ->first();
                        
                        // Fallback to the first available section
                        if (!$targetSection) {
                            $targetSection = Section::where('grade_level_id', $nextGradeLevel->id)->first();
                        }
                        
                        $toSectionId = $targetSection?->id;
                    } else {
                        $decision = 'graduated';
                    }
                } elseif ($decision === 'graduated') {
                    $toGradeLevelId = null;
                    $toSectionId = null;
                } else {
                    // retained or re_exam — stay in the same grade
                    $toGradeLevelId = $section->grade_level_id;
                    $toSectionId = $section->id;
                }

                StudentPromotion::create([
                    'student_id' => $studentId,
                    'from_academic_year_id' => $academicYear->id,
                    'to_academic_year_id' => $nextAcademicYear->id,
                    'from_grade_level_id' => $section->grade_level_id,
                    'to_grade_level_id' => $toGradeLevelId ?? $section->grade_level_id,
                    'to_section_id' => $toSectionId,
                    'status' => $decision,
                    'is_enrolled' => ($decision === 'graduated'), // graduates are finalized immediately
                    'remarks' => $request->remarks[$studentId] ?? null,
                    'processed_by' => auth()->id(),
                ]);

                // Update current enrollment status
                $currentEnrollmentStatus = 'completed';
                if ($decision === 'graduated') {
                    $currentEnrollmentStatus = 'graduated';
                }

                StudentEnrollment::where('student_id', $studentId)
                    ->where('section_id', $section->id)
                    ->where('academic_year_id', $academicYear->id)
                    ->update(['status' => $currentEnrollmentStatus, 'end_date' => now()]);

                if ($decision === 'graduated') {
                    // Update student status to inactive
                    $student = Student::findOrFail($studentId);
                    $student->update(['is_active' => false]);

                    // Create status history log
                    \App\Models\StudentStatusHistory::create([
                        'student_id' => $studentId,
                        'old_status' => 'active',
                        'new_status' => 'graduated',
                        'reason' => 'academic',
                        'notes' => 'Graduated through promotion process.',
                        'effective_date' => now(),
                        'changed_by' => auth()->id(),
                    ]);

                    $graduatedCount++;
                } else {
                    // Do NOT auto-enroll — hold until registrar clicks "Enroll"
                    if ($decision === 'promoted') {
                        $promotedCount++;
                    } elseif ($decision === 're_exam') {
                        $reExamCount++;
                    } else {
                        $retainedCount++;
                    }
                }
            }
        });

        $msg = "Promotion processed: {$promotedCount} promoted, {$retainedCount} retained";
        if ($reExamCount > 0) {
            $msg .= ", {$reExamCount} scheduled for re-exam";
        }
        if ($graduatedCount > 0) {
            $msg .= ", {$graduatedCount} graduated";
        }
        $msg .= ". Students are on hold — use History to enroll them.";

        return redirect()->route('admin.promotions.process')->with('success', $msg);
    }

    /**
     * Registrar enrolls a promoted student into their target section.
     */
    public function enrollStudent(StudentPromotion $studentPromotion)
    {
        if ($studentPromotion->is_enrolled) {
            return redirect()->route('admin.promotions.history')
                ->with('error', 'This student has already been enrolled.');
        }

        if ($studentPromotion->status === 'graduated') {
            return redirect()->route('admin.promotions.history')
                ->with('error', 'Graduated students do not need enrollment.');
        }

        DB::transaction(function() use ($studentPromotion) {
            $toSectionId = $studentPromotion->to_section_id;
            $nextAcademicYear = $studentPromotion->toAcademicYear;

            if ($toSectionId && $nextAcademicYear) {
                StudentEnrollment::updateOrCreate(
                    [
                        'student_id' => $studentPromotion->student_id,
                        'academic_year_id' => $nextAcademicYear->id,
                    ],
                    [
                        'section_id' => $toSectionId,
                        'enrollment_date' => $nextAcademicYear->start_date,
                        'status' => 'active',
                    ]
                );

                // Recalculate roll numbers for the target section
                StudentEnrollment::recalculateRollNumbers($toSectionId, $nextAcademicYear->id);
            }

            $studentPromotion->update(['is_enrolled' => true]);
        });

        $studentName = $studentPromotion->student->full_name ?? 'Student';
        return redirect()->route('admin.promotions.history')
            ->with('success', "{$studentName} has been enrolled successfully.");
    }

    /**
     * Reverse a promotion decision — undo enrollment, restore previous state.
     */
    public function reversePromotion(StudentPromotion $studentPromotion)
    {
        DB::transaction(function() use ($studentPromotion) {
            // 1. Remove any next-year enrollment that was created
            if ($studentPromotion->is_enrolled && $studentPromotion->to_section_id) {
                $deleted = StudentEnrollment::where('student_id', $studentPromotion->student_id)
                    ->where('academic_year_id', $studentPromotion->to_academic_year_id)
                    ->where('section_id', $studentPromotion->to_section_id)
                    ->delete();

                if ($deleted && $studentPromotion->to_section_id) {
                    StudentEnrollment::recalculateRollNumbers(
                        $studentPromotion->to_section_id,
                        $studentPromotion->to_academic_year_id
                    );
                }
            }

            // 2. Restore the previous enrollment to 'active'
            StudentEnrollment::where('student_id', $studentPromotion->student_id)
                ->where('academic_year_id', $studentPromotion->from_academic_year_id)
                ->update(['status' => 'active', 'end_date' => null]);

            // 3. If student was graduated, restore their active status
            if ($studentPromotion->status === 'graduated') {
                Student::where('id', $studentPromotion->student_id)
                    ->update(['is_active' => true]);

                // Remove the graduation status history entry
                \App\Models\StudentStatusHistory::where('student_id', $studentPromotion->student_id)
                    ->where('new_status', 'graduated')
                    ->where('reason', 'academic')
                    ->latest()
                    ->first()
                    ?->delete();
            }

            // 4. Delete the promotion record itself
            $studentPromotion->delete();
        });

        return redirect()->route('admin.promotions.history')
            ->with('success', 'Promotion has been reversed successfully.');
    }

    public function history(Request $request)
    {
        $query = StudentPromotion::with([
            'student', 'fromAcademicYear', 'toAcademicYear', 
            'fromGradeLevel', 'toGradeLevel', 'toSection', 'processor'
        ]);

        if ($request->filled('search')) {
            $query->whereHas('student', function ($q) use ($request) {
                $q->search($request->search);
            });
        }

        if ($request->filled('from_academic_year_id')) {
            $query->where('from_academic_year_id', $request->from_academic_year_id);
        }

        if ($request->filled('to_academic_year_id')) {
            $query->where('to_academic_year_id', $request->to_academic_year_id);
        }

        if ($request->filled('from_grade_level_id')) {
            $query->where('from_grade_level_id', $request->from_grade_level_id);
        }

        if ($request->filled('to_grade_level_id')) {
            $query->where('to_grade_level_id', $request->to_grade_level_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('is_enrolled')) {
            if ($request->is_enrolled === 'graduated') {
                $query->where('status', 'graduated');
            } elseif ($request->is_enrolled === '1') {
                $query->where('is_enrolled', true)->where('status', '!=', 'graduated');
            } elseif ($request->is_enrolled === '0') {
                $query->where('is_enrolled', false);
            }
        }

        $promotions = $query->orderBy('created_at', 'desc')->paginate(50)->withQueryString();

        $academicYears = AcademicYear::orderBy('start_date', 'desc')->get();
        $gradeLevels = GradeLevel::orderBy('sort_order')->get();

        return view('admin.promotions.history', compact('promotions', 'academicYears', 'gradeLevels'));
    }

    /**
     * Registrar bulk enrolls multiple promoted students.
     */
    public function bulkEnroll(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:student_promotions,id',
        ]);

        $promotions = StudentPromotion::with('toAcademicYear')
            ->whereIn('id', $request->ids)
            ->where('is_enrolled', false)
            ->where('status', '!=', 'graduated')
            ->get();

        if ($promotions->isEmpty()) {
            return redirect()->route('admin.promotions.history')
                ->with('error', 'No pending promotion records selected.');
        }

        $enrolledCount = 0;

        DB::transaction(function() use ($promotions, &$enrolledCount) {
            $sectionsToRecalculate = [];

            foreach ($promotions as $promo) {
                $toSectionId = $promo->to_section_id;
                $nextAcademicYear = $promo->toAcademicYear;

                if ($toSectionId && $nextAcademicYear) {
                    StudentEnrollment::updateOrCreate(
                        [
                            'student_id' => $promo->student_id,
                            'academic_year_id' => $nextAcademicYear->id,
                        ],
                        [
                            'section_id' => $toSectionId,
                            'enrollment_date' => $nextAcademicYear->start_date,
                            'status' => 'active',
                        ]
                    );

                    $sectionsToRecalculate[$toSectionId] = $nextAcademicYear->id;
                }

                $promo->update(['is_enrolled' => true]);
                $enrolledCount++;
            }

            foreach ($sectionsToRecalculate as $sectionId => $academicYearId) {
                StudentEnrollment::recalculateRollNumbers($sectionId, $academicYearId);
            }
        });

        return redirect()->route('admin.promotions.history')
            ->with('success', "Successfully enrolled {$enrolledCount} students.");
    }

    /**
     * Registrar bulk reverses multiple promotion decisions.
     */
    public function bulkReverse(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:student_promotions,id',
        ]);

        $promotions = StudentPromotion::whereIn('id', $request->ids)->get();

        if ($promotions->isEmpty()) {
            return redirect()->route('admin.promotions.history')
                ->with('error', 'No promotion records selected.');
        }

        $reversedCount = 0;

        DB::transaction(function() use ($promotions, &$reversedCount) {
            $sectionsToRecalculate = [];

            foreach ($promotions as $promo) {
                // 1. Remove next-year enrollment
                if ($promo->is_enrolled && $promo->to_section_id) {
                    $deleted = StudentEnrollment::where('student_id', $promo->student_id)
                        ->where('academic_year_id', $promo->to_academic_year_id)
                        ->where('section_id', $promo->to_section_id)
                        ->delete();

                    if ($deleted) {
                        $sectionsToRecalculate[$promo->to_section_id] = $promo->to_academic_year_id;
                    }
                }

                // 2. Restore previous enrollment
                StudentEnrollment::where('student_id', $promo->student_id)
                    ->where('academic_year_id', $promo->from_academic_year_id)
                    ->update(['status' => 'active', 'end_date' => null]);

                // 3. If student was graduated, restore status
                if ($promo->status === 'graduated') {
                    Student::where('id', $promo->student_id)
                        ->update(['is_active' => true]);

                    \App\Models\StudentStatusHistory::where('student_id', $promo->student_id)
                        ->where('new_status', 'graduated')
                        ->where('reason', 'academic')
                        ->latest()
                        ->first()
                        ?->delete();
                }

                // 4. Delete promotion record
                $promo->delete();
                $reversedCount++;
            }

            foreach ($sectionsToRecalculate as $sectionId => $academicYearId) {
                StudentEnrollment::recalculateRollNumbers($sectionId, $academicYearId);
            }
        });

        return redirect()->route('admin.promotions.history')
            ->with('success', "Successfully reversed {$reversedCount} promotions.");
    }
}

