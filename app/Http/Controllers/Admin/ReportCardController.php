<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


use App\Models\ReportCardSetting;
use App\Models\Section;
use App\Models\Term;
use App\Models\AcademicYear;
use App\Models\StudentTermRecord;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Andegna\DateTime as EthDateTime;
use Andegna\Constants;
use App\Models\ExportRequest;
use App\Jobs\GenerateSectionReportCards;
use Illuminate\Support\Facades\Storage;

class ReportCardController extends Controller
{
    // Settings
    public function settings()
    {
        $settings = ReportCardSetting::firstOrNew();
        return view('admin.report-cards.settings', compact('settings'));
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'school_name' => 'required|string|max:255',
            'logo' => 'nullable|image|max:2048',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|string|max:255',
            'telephone' => 'nullable|string|max:255',
            'po_box' => 'nullable|string|max:255',
        ]);

        $settings = ReportCardSetting::firstOrNew();
        $settings->fill($request->except('logo', 'template_config'));
        
        // Handle Logo Upload
        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('report-cards', 'public');
            $settings->logo_path = $path;
            // Delete old logo if exists
            if ($settings->logo_path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($settings->logo_path);
            }
            $settings->logo_path = $request->file('logo')->store('report-cards', 'public');
        }

        // Handle JSON config (checkboxes)
        // Just storing simple boolean toggles for now
        $config = [
            'show_rank' => $request->has('show_rank'),
            'show_conduct' => $request->has('show_conduct'),
            'show_attendance' => $request->has('show_attendance'),
            'traits' => $request->get('traits', []),
        ];
        $settings->template_config = $config;
        
        $settings->save();

        return back()->with('success', 'Report card settings updated successfully.');
    }

    public function yearlySettings()
    {
        $settings = ReportCardSetting::firstOrNew();
        return view('admin.report-cards.yearly-settings', compact('settings'));
    }

    public function updateYearlySettings(Request $request)
    {
        $request->validate([
            'evaluation_method' => 'nullable|string',
            'remark' => 'nullable|string',
            'principal_name' => 'nullable|string',
            'parent_instructions' => 'nullable|string',
        ]);

        $settings = ReportCardSetting::firstOrNew();
        
        $config = $settings->yearly_config ?? [];
        $config['evaluation_method'] = $request->evaluation_method;
        $config['remark'] = $request->remark;
        $config['principal_name'] = $request->principal_name;
        $config['parent_instructions'] = $request->parent_instructions;
        
        $settings->yearly_config = $config;
        $settings->save();

        return back()->with('success', 'Yearly report card settings updated successfully.');
    }

    // Data Entry (Conduct, Attendance, Comments)
    public function entry(Request $request, Section $section)
    {
        $academicYear = AcademicYear::findOrFail($request->get('academic_year_id', AcademicYear::where('is_active', true)->value('id')));
        $term = Term::findOrFail($request->get('term_id')); // Require Term ID
        
        // Ensure section belongs to correct year? (Optional check)

        $students = $section->students()->wherePivot('academic_year_id', $academicYear->id)->where('is_active', true)->orderBy('first_name')->get();
        
        // Fetch existing records
        $records = StudentTermRecord::whereIn('student_id', $students->pluck('id'))
            ->where('term_id', $term->id)
            ->get()
            ->keyBy('student_id');

        return view('admin.report-cards.entry', compact('section', 'academicYear', 'term', 'students', 'records'));
    }

    public function storeEntry(Request $request, Section $section)
    {
        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'term_id' => 'required|exists:terms,id',
            'records' => 'array',
        ]);
        
        DB::beginTransaction();
        try {
            foreach($request->records as $studentId => $data) {
                StudentTermRecord::updateOrCreate(
                    [
                        'student_id' => $studentId, 
                        'term_id' => $request->term_id,
                    ],
                    [
                        'academic_year_id' => $request->academic_year_id,
                        'conduct_grade' => $data['conduct'] ?? null,
                        'days_absent' => $data['absent'] ?? 0,
                        'homeroom_teacher_comment' => $data['comment'] ?? null,
                        // 'behavior_traits' => ... (handle later)
                    ]
                );
            }
            DB::commit();
            return back()->with('success', 'Report Card details saved.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error saving details: ' . $e->getMessage());
        }
    }

    // Generate PDF
    public function generatePdf(Request $request, \App\Models\Student $student, \App\Services\GradingService $gradingService)
    {
        $termId = $request->term_id;
        if ($termId === 'yearly') {
            $academicYear = AcademicYear::findOrFail($request->academic_year_id);
            $term = new Term([
                'type' => 'yearly', 
                'name' => 'Yearly Report', 
                'academic_year_id' => $academicYear->id
            ]);
            $term->incrementing = false;
            $term->id = 'yearly';
        } else {
            $term = Term::findOrFail($termId ?? Term::where('is_active', true)->value('id'));
            $academicYear = $term->academicYear;
        }
        
        $settings = ReportCardSetting::first();
        
        if (!$settings) {
            return back()->with('error', 'Report card settings are not configured. Please go to Report Card Settings first.');
        }
        
        // Get Standardized Report Data
        $reportData = $gradingService->getStudentReportData($student, $term, $academicYear);
        
        // Extract variables for the view
        $section = $reportData['section'];
        $subjects = $reportData['subjects'];
        $marks = $reportData['marks'];
        $termRecord = $reportData['record'];
        $totalScore = $reportData['totalScore'];
        $average = $reportData['average'];
        $rank = $reportData['rank'];
        $totalStudents = $reportData['rank_out_of'];
        $isSemester = $reportData['isSemester'];
        $isYearly = $term->type === 'yearly';
        $quarters = collect();
        if ($isSemester) {
            $quarters = $term->quarters()->orderBy('term_number')->get();
        } elseif ($isYearly) {
            $quarters = Term::where('academic_year_id', $academicYear->id)
                ->where('type', 'quarter')
                ->orderBy('term_number')
                ->get();
        }
        $semesters = $isYearly ? Term::where('academic_year_id', $academicYear->id)->where('type', 'semester')->orderBy('start_date')->get() : collect();
        
        // Prepare legacy variables for view compatibility
        $quarterMarks = [];
        $quarterTotals = [];
        $quarterAverages = [];
        $quarterRanks = [];
        $quarterRecords = [];
        
        if ($isSemester || $isYearly) {
            foreach ($reportData['quarters'] as $qId => $qData) {
                foreach ($qData['marks'] as $subId => $score) {
                    $quarterMarks[$subId][$qId] = $score;
                }
                $quarterTotals[$qId] = $qData['total'];
                $quarterAverages[$qId] = $qData['average'];
                $quarterRanks[$qId] = $qData['rank'] . " / " . $totalStudents;
                $quarterRecords[$qId] = $qData['record'];
            }
        }

        $semesterMarks = [];
        $semesterTotals = [];
        $semesterAverages = [];
        $semesterRanks = [];
        if ($isYearly) {
            foreach ($reportData['semesters'] as $sId => $sData) {
                foreach ($sData['marks'] as $subId => $score) {
                    $semesterMarks[$subId][$sId] = $score;
                }
                $semesterTotals[$sId] = $sData['total'];
                $semesterAverages[$sId] = $sData['average'];
                $semesterRanks[$sId] = $sData['rank'] . " / " . $totalStudents;
            }
        }

        // Filter Subjects (Remove those without marks in any pertinent term)
        $subjects = $subjects->filter(function($subject) use ($marks, $quarterMarks, $semesterMarks, $isSemester, $isYearly) {
            if ($isSemester) return isset($quarterMarks[$subject->id]);
            if ($isYearly) return isset($semesterMarks[$subject->id]);
            return isset($marks[$subject->id]);
        });
        
        if ($isYearly) {
            $attendance = $this->getAttendanceSummary($student, $term, $academicYear); // Yearly attendance
            // Calculate Sub-Term Attendance
            $subTermAttendance = $this->calculateSubTermAttendance($student, $quarters, $semesters, $isSemester, $isYearly, $academicYear);
            
            return view('admin.report-cards.yearly-pdf', compact(
                'student', 'term', 'academicYear', 'subjects', 'marks', 'termRecord', 
                'settings', 'totalScore', 'average', 'section', 'rank', 'totalStudents', 
                'isSemester', 'isYearly', 'quarters', 'semesters', 'quarterMarks', 
                'quarterTotals', 'quarterAverages', 'quarterRanks', 'quarterRecords',
                'semesterMarks', 'semesterTotals', 'semesterAverages', 'semesterRanks', 
                'attendance', 'subTermAttendance'
            ));
        }

        // Calculate Sub-Term Attendance
        $subTermAttendance = $this->calculateSubTermAttendance($student, $quarters, $semesters, $isSemester, $isYearly, $academicYear);

        // Return view directly for browser printing
        $attendance = $this->getAttendanceSummary($student, $term, $academicYear);
        return view('admin.report-cards.pdf', compact(
            'student', 'term', 'academicYear', 'subjects', 'marks', 'termRecord', 
            'settings', 'totalScore', 'average', 'section', 'rank', 'totalStudents', 
            'isSemester', 'isYearly', 'quarters', 'semesters', 'quarterMarks', 
            'quarterTotals', 'quarterAverages', 'quarterRanks', 'quarterRecords',
            'semesterMarks', 'semesterTotals', 'semesterAverages', 'semesterRanks', 
            'attendance', 'subTermAttendance'
        ));
    }
    // Bulk Print
    public function bulkPrint(Request $request, Section $section, \App\Services\GradingService $gradingService)
    {
        $academicYear = AcademicYear::findOrFail($request->get('academic_year_id'));
        $termId = $request->get('term_id');
        
        if ($termId === 'yearly') {
            $term = new Term([
                'type' => 'yearly', 
                'name' => 'Yearly Report', 
                'academic_year_id' => $academicYear->id
            ]);
            $term->incrementing = false;
            $term->id = 'yearly';
        } else {
            $term = Term::findOrFail($termId);
        }

        $settings = ReportCardSetting::first();
        
        if (!$settings) {
            return back()->with('error', 'Report card settings are not configured. Please go to Report Card Settings first.');
        }
        
        // Ensure statistics are up to date
        $gradingService->recalculateSectionStatistics($section, $term, $academicYear);
        
        $subjects = $section->gradeLevel->subjects()->orderByPivot('sort_order')->get();
        
        $students = $section->students()
            ->wherePivot('academic_year_id', $academicYear->id)
            ->where('is_active', true)
            ->orderBy('first_name')
            ->get();

        $sectionReportData = $gradingService->getSectionReportData($students, $section, $term, $academicYear);

        $reportCards = [];
        $isSemester = $term->isSemester();
        $isYearly = $term->type === 'yearly';
        $quarters = collect();
        if ($isSemester) {
            $quarters = $term->quarters()->orderBy('term_number')->get();
        } elseif ($isYearly) {
            $quarters = Term::where('academic_year_id', $academicYear->id)
                ->where('type', 'quarter')
                ->orderBy('term_number')
                ->get();
        }
        $semesters = $isYearly ? Term::where('academic_year_id', $academicYear->id)->where('type', 'semester')->orderBy('start_date')->get() : collect();
        
        foreach ($students as $student) {
            $reportData = $sectionReportData[$student->id] ?? null;
            if (!$reportData) continue;
            
            // Extract and prepare for view compatibility
            $marks = $reportData['marks'];
            $termRecord = $reportData['record'];
            $totalScore = $reportData['totalScore'];
            $average = $reportData['average'];
            $rank = $reportData['rank'];
            $totalStudentsCount = $reportData['rank_out_of'];
            
            $studentQuarterMarks = [];
            $studentQuarterStats = [];
            $studentQuarterRecords = [];
            
            if ($isSemester || $isYearly) {
                foreach ($reportData['quarters'] as $qId => $qData) {
                    foreach ($qData['marks'] as $subId => $score) {
                        $studentQuarterMarks[$subId][$qId] = $score;
                    }
                    $studentQuarterStats[$qId] = [
                        'total' => $qData['total'],
                        'avg' => $qData['average'],
                        'rank' => $qData['rank'] . " / " . $totalStudentsCount
                    ];
                    $studentQuarterRecords[$qId] = $qData['record'];
                }
            }

            $studentSemesterMarks = [];
            $studentSemesterStats = [];
            if ($isYearly) {
                foreach ($reportData['semesters'] as $sId => $sData) {
                    foreach ($sData['marks'] as $subId => $score) {
                        $studentSemesterMarks[$subId][$sId] = $score;
                    }
                    $studentSemesterStats[$sId] = [
                        'total' => $sData['total'],
                        'avg' => $sData['average'],
                        'rank' => $sData['rank'] . " / " . $totalStudentsCount
                    ];
                }
            }

            // Filter subjects for this student
            $studentSubjects = $reportData['subjects']->filter(function($subject) use ($marks, $studentQuarterMarks, $studentSemesterMarks, $isSemester, $isYearly) {
                if (!$subject->is_elective) return true;
                if (isset($marks[$subject->id])) return true;
                if ($isSemester && isset($studentQuarterMarks[$subject->id])) {
                    return !empty($studentQuarterMarks[$subject->id]);
                }
                if ($isYearly && isset($studentSemesterMarks[$subject->id])) {
                    return !empty($studentSemesterMarks[$subject->id]);
                }
                return false;
            });

            $reportCards[] = [
                'student' => $student,
                'marks' => $marks,
                'termRecord' => $termRecord,
                'totalScore' => $totalScore,
                'average' => $average,
                'rank' => $rank,
                'studentSubjects' => $studentSubjects,
                'studentQuarterMarks' => $studentQuarterMarks,
                'studentQuarterStats' => $studentQuarterStats,
                'studentQuarterRecords' => collect($studentQuarterRecords),
                'studentSemesterMarks' => $studentSemesterMarks,
                'studentSemesterStats' => $studentSemesterStats,
                'attendance' => $this->getAttendanceSummary($student, $term, $academicYear),
                'subTermAttendance' => $this->calculateSubTermAttendance($student, $quarters, $semesters, $isSemester, $isYearly, $academicYear),
            ];
        }

        $totalStudents = $students->count();

        $viewName = $isYearly ? 'admin.report-cards.bulk-yearly-pdf' : 'admin.report-cards.bulk-pdf';
        
        return view($viewName, compact(
            'section', 'term', 'academicYear', 'subjects', 'reportCards', 'settings', 
            'totalStudents', 'isSemester', 'isYearly', 'quarters', 'semesters'
        ));
    }

    // Background Exports
    public function exports()
    {
        $exports = ExportRequest::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(15);
            
        return view('admin.report-cards.exports', compact('exports'));
    }

    public function bulkExport(Request $request, Section $section)
    {
        $academicYear = AcademicYear::findOrFail($request->get('academic_year_id'));
        $termId = $request->get('term_id');
        
        if ($termId === 'yearly') {
            $term = new Term([
                'type' => 'yearly', 
                'name' => 'Yearly Report', 
                'academic_year_id' => $academicYear->id
            ]);
            $term->incrementing = false;
            $term->id = 'yearly';
        } else {
            $term = Term::findOrFail($termId);
        }

        $exportRequest = ExportRequest::create([
            'user_id' => auth()->id(),
            'type' => 'yearly_report_zip', // or something descriptive
            'status' => 'pending',
            'params' => [
                'section_id' => $section->id,
                'term_id' => $termId,
                'academic_year_id' => $academicYear->id,
                'section_name' => $section->name,
                'term_name' => $term->name,
            ],
        ]);

        GenerateSectionReportCards::dispatch($exportRequest, $section, $term, $academicYear);

        return redirect()->route('admin.report-cards.exports')
            ->with('success', 'Bulk export started in background. You will find it here when finished.');
    }

    public function downloadExport(ExportRequest $exportRequest)
    {
        if ($exportRequest->user_id !== auth()->id()) {
            abort(403);
        }

        if ($exportRequest->status !== 'completed' || !$exportRequest->file_path) {
            return back()->with('error', 'Export is not ready for download.');
        }

        return Storage::disk('public')->download($exportRequest->file_path);
    }

    private function calculateSubTermAttendance($student, $quarters, $semesters, $isSemester, $isYearly, $academicYear)
    {
        $subTermAttendance = [];
        
        if (($isSemester || $isYearly) && $quarters) {
            foreach ($quarters as $q) {
                $subTermAttendance[$q->id] = $this->getAttendanceSummary($student, $q, $academicYear);
            }
        }
        
        if ($isYearly && $semesters) {
             foreach ($semesters as $s) {
                 $subTermAttendance[$s->id] = $this->getAttendanceSummary($student, $s, $academicYear);
             }
        }
        
        return $subTermAttendance;
    }

    private function getAttendanceSummary($student, $term, $academicYear)
    {
        // Define query base
        $query = \App\Models\StudentAttendance::where('student_id', $student->id)
            ->whereHas('section', function($q) use ($academicYear) {
                $q->where('academic_year_id', $academicYear->id);
            });

        // Filter by date range if Term is provided (and not yearly)
        if ($term->type !== 'yearly') {
            $query->whereBetween('attendance_date', [$term->start_date, $term->end_date]);
        }

        $stats = $query->selectRaw('count(*) as total, status')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        // Calculate totals
        $present = $stats['present'] ?? 0;
        $absent = $stats['absent'] ?? 0;
        $late = $stats['late'] ?? 0;
        $excused = $stats['excused'] ?? 0;
        
        // Total days calculation: count distinct dates for this section in this period
        // Or simply sum of all statuses if we assume 1 status per day per student
        $totalDays = $present + $absent + $late + $excused;

        // If we want "Total School Days" regardless of student record (e.g. if they weren't marked),
        // we'd need to query distinct dates from the whole table for this section.
        // For individual report, sum of records is safer as "Days on Roll".

        return [
            'total_days' => $totalDays,
            'present' => $present,
            'absent' => $absent,
            'late' => $late,
            'excused' => $excused
        ];
    }
}
