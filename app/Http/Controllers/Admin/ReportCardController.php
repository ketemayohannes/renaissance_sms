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

        return back()->with('success', 'Report Card Settings updated.');
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
    public function generatePdf(Request $request, \App\Models\Student $student)
    {
        $termId = $request->term_id ?? Term::where('is_active', true)->value('id');
        $term = Term::findOrFail($termId);
        $academicYear = $term->academicYear;
        
        $settings = ReportCardSetting::first();
        
        // Fetch Marks (Similar to ReportCardController but for one student)
        // We need: Subjects, Marks, Total, Average, Rank
        
        // This logic mimics SectionGradeController but for one student
        // Ideally we should move calculation logic to a Service.
        
        // Fetch Sections to know grade level
        $enrollment = $student->enrollments()->where('academic_year_id', $academicYear->id)->first();
        if(!$enrollment) return back()->with('error', 'Student not enrolled in this year.');
        $section = $enrollment->section;
        $gradeLevel = $section->gradeLevel;
        
        // Fetch Subjects
        $subjects = $gradeLevel->subjects; // Filter electives logic needed?

        // Fetch Marks
        $marks = \App\Models\StudentMark::where('student_id', $student->id)
            ->where('term_id', $term->id)
            ->get()
            ->pluck('score', 'subject_id'); // If using new system, score column

        // Student Term Record (Conduct, etc)
        $termRecord = StudentTermRecord::where('student_id', $student->id)
            ->where('term_id', $term->id)
            ->first();

        // Calculate Rank
        $allMarks = \App\Models\StudentMark::whereIn('student_id', $section->students()->pluck('students.id'))
            ->where('term_id', $term->id)
            ->get();
            
        $studentTotals = $allMarks->groupBy('student_id')->map(function ($marks) {
            return $marks->sum('score');
        })->sortDesc();
        
        $rank = $studentTotals->keys()->search($student->id);
        $rank = $rank !== false ? $rank + 1 : '-';
        $totalStudents = $studentTotals->count();

        // Check if Semester
        $isSemester = $term->type === 'semester';
        $quarters = collect();
        $quarterMarks = []; // [subject_id => [quarter_id => score]]
        $quarterTotals = []; // [quarter_id => total]
        $quarterAverages = []; // [quarter_id => avg]
        $quarterRanks = []; // [quarter_id => "rank / total"]
        
        if ($isSemester) {
            $quarters = $term->quarters()->orderBy('term_number')->get();
            $quarterIds = $quarters->pluck('id');

            // 1. Fetch Student Marks for Quarters
            $rawStudentMarks = \App\Models\StudentMark::where('student_id', $student->id)
                ->whereIn('term_id', $quarterIds)
                ->get();
            
            foreach ($rawStudentMarks as $mark) {
                $quarterMarks[$mark->subject_id][$mark->term_id] = $mark->score;
            }

            // 2. Calculate Totals and Averages for Student per Quarter
            foreach ($quarters as $quarter) {
                $qTotal = $rawStudentMarks->where('term_id', $quarter->id)->sum('score');
                $qCount = $rawStudentMarks->where('term_id', $quarter->id)->count();
                $quarterTotals[$quarter->id] = $qTotal;
                $quarterAverages[$quarter->id] = $qCount > 0 ? $qTotal / $qCount : 0;
            }

            // 3. Calculate Ranks for each Quarter (Need all students in section)
            $sectionStudentIds = $section->students()->pluck('students.id');
            $allQuarterMarks = \App\Models\StudentMark::whereIn('student_id', $sectionStudentIds)
                ->whereIn('term_id', $quarterIds)
                ->select('student_id', 'term_id', 'score')
                ->get();
            
            foreach ($quarters as $quarter) {
                // Group marks by student for this quarter
                $qStudentTotals = $allQuarterMarks->where('term_id', $quarter->id)
                    ->groupBy('student_id')
                    ->map(function ($marks) {
                        return $marks->sum('score');
                    })
                    ->sortDesc();
                
                $myRank = $qStudentTotals->keys()->search($student->id);
                $myRank = $myRank !== false ? $myRank + 1 : '-';
                $qTotalStudents = $qStudentTotals->count();
                $quarterRanks[$quarter->id] = "$myRank / $qTotalStudents";
            }
        }

        // Filter Subjects (Remove those without marks in current term OR any quarter)
        $subjects = $subjects->filter(function($subject) use ($marks, $quarterMarks, $isSemester) {
            if ($isSemester) {
                // Keep if has marks in any quarter
                return isset($quarterMarks[$subject->id]);
            }
            return isset($marks[$subject->id]);
        });
        
        $totalScore = $marks->sum();
        $average = $marks->count() > 0 ? $totalScore / $marks->count() : 0;
        
        // Return view directly for browser printing
        return view('admin.report-cards.pdf', compact('student', 'term', 'academicYear', 'subjects', 'marks', 'termRecord', 'settings', 'totalScore', 'average', 'section', 'rank', 'totalStudents', 'isSemester', 'quarters', 'quarterMarks', 'quarterTotals', 'quarterAverages', 'quarterRanks'));
    }
    // Bulk Print
    public function bulkPrint(Request $request, Section $section)
    {
        $academicYear = AcademicYear::findOrFail($request->get('academic_year_id'));
        $term = Term::findOrFail($request->get('term_id'));
        $settings = ReportCardSetting::first();
        
        $students = $section->students()->wherePivot('academic_year_id', $academicYear->id)->where('is_active', true)->orderBy('first_name')->get();
        $gradeLevel = $section->gradeLevel;
        
        // Fix: Ensure we get subjects even if none are explicitly assigned or fetch all for grade level
        $subjects = $gradeLevel->subjects;

        // Fetch ALL marks for this section/term efficiently
        $allMarks = \App\Models\StudentMark::whereIn('student_id', $students->pluck('id'))
            ->where('term_id', $term->id)
            ->get()
            ->groupBy('student_id');

        // Fetch ALL term records
        $allRecords = StudentTermRecord::whereIn('student_id', $students->pluck('id'))
            ->where('term_id', $term->id)
            ->get()
            ->keyBy('student_id');

        // Calculate Ranks for all students
        $studentTotals = $allMarks->map(function ($marks) {
            return $marks->sum('score');
        })->sortDesc();
        
        $ranks = [];
        $currentRank = 1;
        foreach ($studentTotals as $studentId => $total) {
            $ranks[$studentId] = $currentRank++;
        }
        $totalStudents = $students->count();

        $allQuarterMarks = []; // [student_id => [subject_id => [quarter_id => score]]]
        $allQuarterStats = []; // [student_id => [quarter_id => ['total' => x, 'avg' => y, 'rank' => z]]]
        $quarters = collect();

        // Define isSemester
        $isSemester = $term->type === 'semester';

        
        if ($isSemester) {
            $quarters = $term->quarters()->orderBy('term_number')->get();
            $quarterIds = $quarters->pluck('id');

            // Fetch ALL marks or quarters efficiently
            $rawQuarterMarks = \App\Models\StudentMark::whereIn('student_id', $students->pluck('id'))
                ->whereIn('term_id', $quarterIds)
                ->get();
            
            // 1. Group Marks: Student -> Subject -> Quarter
            foreach ($rawQuarterMarks as $mark) {
                if (!isset($allQuarterMarks[$mark->student_id])) {
                    $allQuarterMarks[$mark->student_id] = [];
                }
                if (!isset($allQuarterMarks[$mark->student_id][$mark->subject_id])) {
                    $allQuarterMarks[$mark->student_id][$mark->subject_id] = [];
                }
                $allQuarterMarks[$mark->student_id][$mark->subject_id][$mark->term_id] = $mark->score;
            }

            // 2. Calculate Totals, Averages, and Ranks per Quarter
            foreach ($quarters as $quarter) {
                // Determine totals for this quarter for ALL students to rank them
                $qStudentTotals = $rawQuarterMarks->where('term_id', $quarter->id)
                    ->groupBy('student_id')
                    ->map(function ($marks) {
                        return $marks->sum('score');
                    })
                    ->sortDesc();
                
                $currentRank = 1;
                $qRanks = [];
                foreach ($qStudentTotals as $sId => $total) {
                    $qRanks[$sId] = $currentRank++;
                }
                $qTotalStudents = $students->count(); // Use section count or count of students with marks? Usually section count.

                // Store stats
                foreach ($students as $student) {
                    $sTotal = $qStudentTotals[$student->id] ?? 0;
                    $sCount = $rawQuarterMarks->where('student_id', $student->id)->where('term_id', $quarter->id)->count();
                    $sAvg = $sCount > 0 ? $sTotal / $sCount : 0;
                    $sRank = ($qRanks[$student->id] ?? '-') . " / " . $qTotalStudents;

                    $allQuarterStats[$student->id][$quarter->id] = [
                        'total' => $sTotal,
                        'avg' => $sAvg,
                        'rank' => $sRank
                    ];
                }
            }
        }

        // Prepare data structure for view
        $reportCards = [];
        foreach($students as $student) {
            $studentMarks = $allMarks->get($student->id, collect());
            $marks = $studentMarks->pluck('score', 'subject_id');
            $termRecord = $allRecords->get($student->id);
            
            $totalScore = $marks->sum();
            $average = $marks->count() > 0 ? $totalScore / $marks->count() : 0;
            
            $studentQuarterMarks = $allQuarterMarks[$student->id] ?? [];
            $studentQuarterStats = $allQuarterStats[$student->id] ?? [];

            // Filter subjects for this student
            $studentSubjects = $subjects->filter(function($subject) use ($marks, $studentQuarterMarks, $isSemester) {
                if ($isSemester) {
                    return isset($studentQuarterMarks[$subject->id]);
                }
                return isset($marks[$subject->id]);
            });

            $rank = $ranks[$student->id] ?? '-';

            $reportCards[] = compact('student', 'marks', 'termRecord', 'totalScore', 'average', 'rank', 'studentSubjects', 'studentQuarterMarks', 'studentQuarterStats');
        }

        return view('admin.report-cards.bulk-pdf', compact('section', 'term', 'academicYear', 'subjects', 'reportCards', 'settings', 'totalStudents', 'isSemester', 'quarters'));
    }
}
