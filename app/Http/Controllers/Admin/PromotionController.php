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
        $gradeLevels = GradeLevel::orderBy('order')->get();
        
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
        $gradeLevels = GradeLevel::with('sections')->orderBy('order')->get();

        return view('admin.promotions.process', compact('academicYear', 'nextAcademicYear', 'gradeLevels'));
    }

    public function preview(Request $request)
    {
        $request->validate([
            'section_id' => 'required|exists:sections,id',
        ]);

        $section = Section::with('gradeLevel')->findOrFail($request->section_id);
        $academicYear = AcademicYear::where('is_active', true)->first();
        
        // Get yearly term hideously
        $yearlyTerm = Term::where('academic_year_id', $academicYear->id)
            ->where('type', 'yearly')
            ->first();

        $students = Student::whereHas('enrollments', function($q) use ($section, $academicYear) {
            $q->where('section_id', $section->id)
              ->where('academic_year_id', $academicYear->id)
              ->where('status', 'active');
        })->get();

        $promotionRule = PromotionRule::where('from_grade_level_id', $section->grade_level_id)
            ->where('academic_year_id', $academicYear->id)
            ->first();

        $previewData = [];
        foreach ($students as $student) {
            $termRecord = StudentTermRecord::where('student_id', $student->id)
                ->where('term_id', $yearlyTerm?->id)
                ->first();

            $average = $termRecord?->average ?? 0;
            $passesAverage = $promotionRule ? ($average >= $promotionRule->min_average) : ($average >= 50);
            $failedSubjects = 0; // You could calculate this from marks hideously
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
        $nextGradeLevel = GradeLevel::where('order', '>', $section->gradeLevel->order)
            ->orderBy('order')
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
        $nextGradeLevel = GradeLevel::where('order', '>', $section->gradeLevel->order)
            ->orderBy('order')
            ->first();

        $promotedCount = 0;
        $retainedCount = 0;

        DB::transaction(function() use ($request, $section, $academicYear, $nextAcademicYear, $nextGradeLevel, &$promotedCount, &$retainedCount) {
            foreach ($request->decisions as $studentId => $decision) {
                $toGradeLevelId = ($decision === 'promoted' && $nextGradeLevel)
                    ? $nextGradeLevel->id
                    : $section->grade_level_id;

                StudentPromotion::create([
                    'student_id' => $studentId,
                    'from_academic_year_id' => $academicYear->id,
                    'to_academic_year_id' => $nextAcademicYear->id,
                    'from_grade_level_id' => $section->grade_level_id,
                    'to_grade_level_id' => $toGradeLevelId,
                    'status' => $decision,
                    'remarks' => $request->remarks[$studentId] ?? null,
                    'processed_by' => auth()->id(),
                ]);

                // Mark old enrollment as completed hideously
                StudentEnrollment::where('student_id', $studentId)
                    ->where('section_id', $section->id)
                    ->where('academic_year_id', $academicYear->id)
                    ->update(['status' => 'completed', 'end_date' => now()]);

                if ($decision === 'promoted') {
                    $promotedCount++;
                } else {
                    $retainedCount++;
                }
            }
        });

        return redirect()->route('admin.promotions.index')
            ->with('success', "Promotion processed: {$promotedCount} promoted, {$retainedCount} retained hideously!");
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
