<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\GradeLevel;
use App\Models\Section;
use App\Models\StudentMark;
use App\Models\Term;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AcademicReportController extends Controller
{
    public function index()
    {
        $academicYears = AcademicYear::latest()->get();
        $gradeLevels = GradeLevel::all();
        return view('admin.academic-reports.index', compact('academicYears', 'gradeLevels'));
    }

    public function settings()
    {
        $settings = \App\Models\AcademicReportSetting::firstOrNew();
        $subjects = \App\Models\Subject::with('gradeLevels')->where('is_active', true)->orderBy('name')->get();
        $gradeLevels = \App\Models\GradeLevel::orderBy('sort_order')->get();
        return view('admin.academic-reports.settings', compact('settings', 'subjects', 'gradeLevels'));
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'school_name' => 'nullable|string|max:255',
            'roster_logo' => 'nullable|image|max:2048',
            'subject_order' => 'nullable|array',
        ]);

        $settings = \App\Models\AcademicReportSetting::firstOrNew();
        $settings->school_name = $request->school_name;

        if ($request->hasFile('roster_logo')) {
            // Delete old logo if exists
            if ($settings->roster_logo_path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($settings->roster_logo_path);
            }
            $settings->roster_logo_path = $request->file('roster_logo')->store('academic-reports', 'public');
        }

        // Save subject order
        $displayOptions = $settings->display_options ?? [];
        if ($request->has('subject_order')) {
            // Filter out empty values and ensure integers
            $displayOptions['subject_order'] = collect($request->subject_order)
                ->filter(fn($val) => $val !== null && $val !== '')
                ->map(fn($val) => (int)$val)
                ->toArray();
        }
        $settings->display_options = $displayOptions;

        $settings->save();

        return back()->with('success', 'Roster settings updated successfully.');
    }

    public function show(Request $request, \App\Services\GradingService $gradingService)
    {
        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'term_id' => 'required', // Relaxed to allow 'yearly'
            'section_id' => 'required|exists:sections,id',
        ]);

        $academicYear = AcademicYear::findOrFail($request->academic_year_id);
        
        $termId = $request->term_id;
        if ($termId === 'yearly') {
            $term = new Term([
                'type' => 'yearly', 
                'name' => 'Yearly', 
                'academic_year_id' => $academicYear->id
            ]);
            $term->incrementing = false;
            $term->id = 'yearly';
        } else {
            $term = Term::findOrFail($termId);
        }
        $section = Section::findOrFail($request->section_id);
        $gradeLevel = $section->gradeLevel;
        $subjects = $gradeLevel->subjects()->orderByPivot('sort_order')->get();

        // Apply custom subject order if exists
        $settings = \App\Models\AcademicReportSetting::first();
        if ($settings && isset($settings->display_options['subject_order'])) {
            $orderMap = $settings->display_options['subject_order'];
            $subjects = $subjects->sort(function($a, $b) use ($orderMap) {
                $orderA = $orderMap[$a->id] ?? 999;
                $orderB = $orderMap[$b->id] ?? 999;
                return $orderA <=> $orderB;
            });
        }

        $students = $section->students()
            ->wherePivot('academic_year_id', $academicYear->id)
            ->where('is_active', true)
            ->orderBy('first_name')
            ->get();

        // Ensure statistics are up to date
        $gradingService->recalculateSectionStatistics($section, $term, $academicYear);
        
        $reports = [];
        foreach ($students as $student) {
            $reportData = $gradingService->getStudentReportData($student, $term, $academicYear);
            $totalStudentsCount = $reportData['rank_out_of'];
            
            if ($term->isSemester()) {
                $rows = [];
                $qIdx = 1;
                foreach ($reportData['quarters'] as $qId => $qData) {
                    $absence = $qData['record']->days_absent ?? null;
                    $rows['q' . $qIdx] = [
                        'label' => $qData['term']->name,
                        'marks' => $qData['marks'],
                        'total' => $qData['total'],
                        'average' => $qData['average'],
                        'conduct' => $qData['record']->conduct_grade ?? 'A',
                        'absence' => ($absence === null || $absence === '' || $absence == 0) ? '_' : $absence,
                        'rank' => $qData['rank']
                    ];
                    $qIdx++;
                }
                
                $semAbsence = $reportData['record']->days_absent ?? null;
                $rows['avg'] = [
                    'label' => 'Sem Avg',
                    'marks' => $reportData['marks'],
                    'total' => $reportData['totalScore'],
                    'average' => $reportData['average'],
                    'conduct' => '-',
                    'absence' => ($semAbsence === null || $semAbsence === '' || $semAbsence == 0) ? '_' : $semAbsence,
                    'rank' => $reportData['rank']
                ];
                
                $reports[] = [
                    'student' => $student,
                    'gender' => $student->gender,
                    'rows' => $rows
                ];
            } elseif ($term->type === 'yearly') {
                // Get all semesters and their quarters
                $semesters = Term::where('academic_year_id', $academicYear->id)
                    ->where('type', 'semester')
                    ->orderBy('start_date')
                    ->get();
                
                $rows = [];
                $yearlySubjectTotals = [];
                $yearlySubjectCounts = [];
                
                foreach ($semesters as $semester) {
                    $quarters = $semester->quarters()->orderBy('term_number')->get();
                    $semesterSubjectTotals = [];
                    $semesterSubjectCounts = [];
                    
                    foreach ($quarters as $quarter) {
                        // Get quarter data
                        $qRecord = \App\Models\StudentTermRecord::where('student_id', $student->id)
                            ->where('term_id', $quarter->id)
                            ->first();
                        
                        $qMarks = \App\Models\StudentMark::where('student_id', $student->id)
                            ->where('term_id', $quarter->id)
                            ->get()
                            ->pluck('score', 'subject_id');
                        
                        $qAbsence = $qRecord->days_absent ?? null;
                        $rows['q' . $quarter->term_number] = [
                            'label' => 'Quarter ' . $this->romanNumeral($quarter->term_number),
                            'marks' => $qMarks,
                            'total' => $qRecord->total_score ?? $qMarks->sum(),
                            'average' => $qRecord->average_score ?? ($subjects->count() > 0 ? $qMarks->sum() / $subjects->count() : 0),
                            'conduct' => $qRecord->conduct_grade ?? 'A',
                            'absence' => ($qAbsence === null || $qAbsence === '' || $qAbsence == 0) ? '_' : $qAbsence,
                            'rank' => $qRecord->rank ?? '-'
                        ];
                        
                        // Accumulate for semester average
                        foreach ($qMarks as $subId => $score) {
                            if (!isset($semesterSubjectTotals[$subId])) {
                                $semesterSubjectTotals[$subId] = 0;
                                $semesterSubjectCounts[$subId] = 0;
                            }
                            $semesterSubjectTotals[$subId] += $score;
                            $semesterSubjectCounts[$subId]++;
                        }
                    }
                    
                    // Calculate semester averages
                    $semMarks = collect();
                    foreach ($semesterSubjectTotals as $subId => $total) {
                        $semMarks[$subId] = $total / $semesterSubjectCounts[$subId];
                        
                        // Also accumulate for yearly
                        if (!isset($yearlySubjectTotals[$subId])) {
                            $yearlySubjectTotals[$subId] = 0;
                            $yearlySubjectCounts[$subId] = 0;
                        }
                        $yearlySubjectTotals[$subId] += $semMarks[$subId];
                        $yearlySubjectCounts[$subId]++;
                    }
                    
                    $sRecord = \App\Models\StudentTermRecord::where('student_id', $student->id)
                        ->where('term_id', $semester->id)
                        ->first();
                    
                    $sAbsence = $sRecord->days_absent ?? null;
                    $rows['s' . $semester->term_number] = [
                        'label' => 'Sem Avg',
                        'marks' => $semMarks,
                        'total' => $sRecord->total_score ?? $semMarks->sum(),
                        'average' => $sRecord->average_score ?? ($subjects->count() > 0 ? $semMarks->sum() / $subjects->count() : 0),
                        'conduct' => '-',
                        'absence' => ($sAbsence === null || $sAbsence === '' || $sAbsence == 0) ? '_' : $sAbsence,
                        'rank' => $sRecord->rank ?? '-'
                    ];
                }
                
                // Calculate yearly averages
                $yearMarks = collect();
                foreach ($yearlySubjectTotals as $subId => $total) {
                    $yearMarks[$subId] = $total / $yearlySubjectCounts[$subId];
                }
                
                $yearTotal = $yearMarks->sum();
                $rows['avg'] = [
                    'label' => 'Year Avg',
                    'marks' => $yearMarks,
                    'total' => $yearTotal,
                    'average' => $subjects->count() > 0 ? $yearTotal / $subjects->count() : 0,
                    'conduct' => '-',
                    'absence' => '_',
                    'rank' => '-'  // Will be calculated after all students processed
                ];

                $reports[] = [
                    'student' => $student,
                    'gender' => $student->gender,
                    'rows' => $rows,
                    'yearTotal' => $yearTotal  // Store for rank calculation
                ];

            } else {
                $qtrAbsence = $reportData['record']->days_absent ?? null;
                $reports[] = [
                    'student' => $student,
                    'marks' => $reportData['marks'],
                    'total' => $reportData['totalScore'],
                    'average' => $reportData['average'],
                    'gender' => $student->gender,
                    'conduct' => $reportData['record']->conduct_grade ?? '',
                    'absence' => ($qtrAbsence === null || $qtrAbsence === '' || $qtrAbsence == 0) ? '_' : $qtrAbsence,
                    'rank' => $reportData['rank'],
                    'rows' => [
                        'q1' => [
                            'label' => $term->name,
                            'marks' => $reportData['marks'],
                            'total' => $reportData['totalScore'],
                            'average' => $reportData['average'],
                            'conduct' => $reportData['record']->conduct_grade ?? '',
                            'absence' => ($qtrAbsence === null || $qtrAbsence === '' || $qtrAbsence == 0) ? '_' : $qtrAbsence,
                            'rank' => $reportData['rank']
                        ]
                    ]
                ];
            }
        }

        // Calculate yearly ranks if this is a yearly report
        if ($term->type === 'yearly') {
            $totalStudentsCount = count($reports);
            $sortedTotals = collect($reports)->pluck('yearTotal')->sortDesc();
            
            foreach ($reports as &$report) {
                $yearTotal = $report['yearTotal'] ?? 0;
                $rank = $sortedTotals->filter(fn($score) => $score > $yearTotal)->count() + 1;
                $report['rows']['avg']['rank'] = $rank;
            }
            unset($report); // Break reference
        }

        // Final subject filtering for the whole roster
        $subjects = $subjects->filter(function($subject) use ($reports) {
            if (!$subject->is_elective) return true;
            foreach ($reports as $r) {
                if (isset($r['rows'])) {
                    foreach ($r['rows'] as $row) {
                        if (isset($row['marks'][$subject->id])) return true;
                    }
                } elseif (isset($r['marks'][$subject->id])) {
                    return true;
                }
            }
            return false;
        });

        $reportType = $request->get('report_type', 'roster');

        if ($reportType === 'report_card') {
            return redirect()->route('admin.section-grades.bulk-print-report-cards', [
                'section' => $section->id,
                'academic_year_id' => $academicYear->id,
                'term_id' => $termId
            ]);
        }

        if ($reportType === 'result_analysis') {
            return view('admin.academic-reports.analysis', compact('academicYear', 'term', 'section', 'subjects', 'reports'));
        }

        $generalSettings = \App\Models\ReportCardSetting::first();

        return view('admin.academic-reports.show', compact('academicYear', 'term', 'section', 'subjects', 'reports', 'settings', 'generalSettings'));
    }

    private function romanNumeral($num)
    {
        $numerals = [1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV'];
        return $numerals[$num] ?? $num;
    }
}
