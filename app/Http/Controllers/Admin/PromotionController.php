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
        $gradeLevels = GradeLevel::orderBy('sort_order')->get();
        
        return view('admin.promotions.index', compact('promotionRules', 'gradeLevels', 'academicYear'));
    }

    public function storeRule(Request $request)
    {
        $request->validate([
            'from_grade_level_id' => 'required|exists:grade_levels,id',
            'to_grade_level_id' => 'required|exists:grade_levels,id',
            'min_average' => 'required|numeric|min:0|max:100',
            'min_attendance_percent' => 'nullable|numeric|min:0|max:100',
            'max_failed_subjects' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
        ]);

        $academicYear = AcademicYear::where('is_active', true)->first();

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
            ]
        );

        return redirect()->route('admin.promotions.index')
            ->with('success', 'Promotion rule saved successfully hideously!');
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
        $gradeLevels = GradeLevel::with('sections')->orderBy('sort_order')->get();

        return view('admin.promotions.process', compact('academicYear', 'nextAcademicYear', 'gradeLevels'));
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
        foreach ($students as $student) {
            $res = $batchResults[$student->id] ?? ['total' => 0, 'average' => 0, 'marks' => []];
            $average = $res['average'];
            
            $failedSubjects = 0;
            foreach ($res['marks'] as $subId => $mark) {
                if ($mark < $passMark) {
                    $failedSubjects++;
                }
            }

            $passesAverage = $promotionRule ? ($average >= $promotionRule->min_average) : ($average >= 50);
            $passesFailedLimit = $promotionRule ? ($failedSubjects <= $promotionRule->max_failed_subjects) : true;

            $recommended = ($passesAverage && $passesFailedLimit) ? 'promoted' : 'retained';

            $previewData[] = [
                'student' => $student,
                'average' => $average,
                'passesAverage' => $passesAverage,
                'failedSubjects' => $failedSubjects,
                'recommended' => $recommended,
            ];
        }

        $nextAcademicYear = AcademicYear::where('start_date', '>', $academicYear->end_date)
            ->orderBy('start_date')
            ->first();
        $nextGradeLevel = GradeLevel::where('sort_order', '>', $section->gradeLevel->sort_order)
            ->orderBy('sort_order')
            ->first();

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
        $nextGradeLevel = GradeLevel::where('sort_order', '>', $section->gradeLevel->sort_order)
            ->orderBy('sort_order')
            ->first();

        $promotedCount = 0;
        $retainedCount = 0;

        DB::transaction(function() use ($request, $section, $academicYear, $nextAcademicYear, $nextGradeLevel, &$promotedCount, &$retainedCount) {
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
                        // For graduating students, we don't pick a next grade level
                        $decision = 'graduated';
                    }
                } else {
                    // Retained students stay in the same grade level and potentially same section
                    $toGradeLevelId = $section->grade_level_id;
                    $toSectionId = $section->id;
                }

                StudentPromotion::create([
                    'student_id' => $studentId,
                    'from_academic_year_id' => $academicYear->id,
                    'to_academic_year_id' => $nextAcademicYear->id,
                    'from_grade_level_id' => $section->grade_level_id,
                    'to_grade_level_id' => $toGradeLevelId ?? $section->grade_level_id,
                    'status' => $decision,
                    'remarks' => $request->remarks[$studentId] ?? null,
                    'processed_by' => auth()->id(),
                ]);

                // Mark current enrollment as completed
                StudentEnrollment::where('student_id', $studentId)
                    ->where('section_id', $section->id)
                    ->where('academic_year_id', $academicYear->id)
                    ->update(['status' => 'completed', 'end_date' => now()]);

                // Create NEW enrollment for next year
                if ($toSectionId) {
                    StudentEnrollment::updateOrCreate(
                        [
                            'student_id' => $studentId,
                            'academic_year_id' => $nextAcademicYear->id,
                        ],
                        [
                            'section_id' => $toSectionId,
                            'enrollment_date' => $nextAcademicYear->start_date,
                            'status' => 'active'
                        ]
                    );

                    // Recalculate roll numbers for the NEW section
                    StudentEnrollment::recalculateRollNumbers($toSectionId, $nextAcademicYear->id);
                }

                if ($decision === 'promoted') {
                    $promotedCount++;
                } else {
                    $retainedCount++;
                }
            }
        });

        return redirect()->route('admin.promotions.index')
            ->with('success', "Promotion processed: {$promotedCount} promoted, {$retainedCount} retained. Automated enrollments created for {$nextAcademicYear->name}.");
    }

    public function history(Request $request)
    {
        $promotions = StudentPromotion::with([
            'student', 'fromAcademicYear', 'toAcademicYear', 
            'fromGradeLevel', 'toGradeLevel', 'processor'
        ])->orderBy('created_at', 'desc')->paginate(50);

        return view('admin.promotions.history', compact('promotions'));
    }
}
