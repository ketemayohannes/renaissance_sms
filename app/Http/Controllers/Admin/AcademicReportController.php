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

    public function show(Request $request)
    {
        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'term_id' => 'required|exists:terms,id',
            'section_id' => 'required|exists:sections,id',
        ]);

        $academicYear = AcademicYear::findOrFail($request->academic_year_id);
        $term = Term::findOrFail($request->term_id);
        $section = Section::findOrFail($request->section_id);
        $gradeLevel = $section->gradeLevel;
        $subjects = $gradeLevel->subjects;

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

        if ($term->isSemester()) {
            $quarters = $term->quarters()->orderBy('term_number')->get();
            $quarterIds = $quarters->pluck('id')->toArray();
            
            // Fetch all marks for this section and all quarters in the semester
            $marksCollection = StudentMark::where('academic_year_id', $academicYear->id)
                ->whereIn('term_id', $quarterIds)
                ->whereIn('student_id', $students->pluck('id'))
                ->get();
                
            $allMarks = $marksCollection->groupBy('student_id');
            
            // Filter subjects: hide electives with no data in ANY quarter
            $subjectIdsWithData = $marksCollection->pluck('subject_id')->unique()->toArray();
            $subjects = $subjects->filter(function($subject) use ($subjectIdsWithData) {
                return !$subject->is_elective || in_array($subject->id, $subjectIdsWithData);
            });

            $termRecords = \App\Models\StudentTermRecord::whereIn('term_id', array_merge($quarterIds, [$term->id]))
                ->whereIn('student_id', $students->pluck('id'))
                ->get()
                ->groupBy('student_id');

            $reports = [];
            $subjectIds = $subjects->pluck('id')->toArray();
            
            foreach ($students as $student) {
                $studentMarks = $allMarks->get($student->id, collect());
                $studentRecords = $termRecords->get($student->id, collect());
                
                $rows = [];
                // Row 1 & 2: Quarters
                foreach ($quarters as $index => $q) {
                    $qMarks = $studentMarks->filter(fn($m) => $m->term_id == $q->id && in_array($m->subject_id, $subjectIds))
                        ->pluck('score', 'subject_id');
                    
                    $total = $qMarks->sum();
                    $count = $qMarks->count();
                    $average = $count > 0 ? $total / $count : 0;
                    $record = $studentRecords->firstWhere('term_id', $q->id);

                    $rows['q' . ($index + 1)] = [
                        'label' => $q->name,
                        'marks' => $qMarks,
                        'total' => $total,
                        'average' => $average,
                        'conduct' => $record->conduct_grade ?? '',
                        'absence' => $record->days_absent ?? '',
                        'rank' => 0
                    ];
                }

                // Row 3: Semester Average
                $semMarks = collect();
                foreach ($subjectIds as $subId) {
                    $q1 = $rows['q1']['marks'][$subId] ?? null;
                    $q2 = $rows['q2']['marks'][$subId] ?? null;
                    if ($q1 !== null || $q2 !== null) {
                        $avg = (($q1 ?? 0) + ($q2 ?? 0)) / (($q1 !== null && $q2 !== null) ? 2 : 1);
                        $semMarks[$subId] = $avg;
                    }
                }

                $semTotal = $semMarks->sum();
                $semCount = $semMarks->count();
                $semAverage = $semCount > 0 ? $semTotal / $semCount : 0;
                $semRecord = $studentRecords->firstWhere('term_id', $term->id);

                $rows['avg'] = [
                    'label' => 'Sem Avg',
                    'marks' => $semMarks,
                    'total' => $semTotal,
                    'average' => $semAverage,
                    'conduct' => $semRecord->conduct_grade ?? '',
                    'absence' => $semRecord->days_absent ?? '',
                    'rank' => 0
                ];

                $reports[] = [
                    'student' => $student,
                    'gender' => $student->gender,
                    'rows' => $rows
                ];
            }

            // Calculate Ranks for each row type
            foreach (['q1', 'q2', 'avg'] as $type) {
                if (isset($reports[0]['rows'][$type])) {
                    usort($reports, fn($a, $b) => $b['rows'][$type]['total'] <=> $a['rows'][$type]['total']);
                    $prevTotal = null;
                    $actualRank = 1;
                    foreach ($reports as $index => &$report) {
                        if ($prevTotal !== null && $report['rows'][$type]['total'] < $prevTotal) {
                            $actualRank = $index + 1;
                        }
                        $report['rows'][$type]['rank'] = $actualRank;
                        $prevTotal = $report['rows'][$type]['total'];
                    }
                }
            }

            // Final sort by name
            usort($reports, fn($a, $b) => strcmp($a['student']->full_name, $b['student']->full_name));

        } else {
            // Existing Quarter Roster logic
            // Fetch all marks for this section and term
            $marksCollection = StudentMark::where('academic_year_id', $academicYear->id)
                ->where('term_id', $term->id)
                ->whereIn('student_id', $students->pluck('id'))
                ->get();
                
            $allMarks = $marksCollection->groupBy('student_id');
            
            // Filter subjects: hide electives with no data
            $subjectIdsWithData = $marksCollection->pluck('subject_id')->unique()->toArray();
            $subjects = $subjects->filter(function($subject) use ($subjectIdsWithData) {
                return !$subject->is_elective || in_array($subject->id, $subjectIdsWithData);
            });

            // Fetch Student Term Records (Conduct, Attendance)
            $termRecords = \App\Models\StudentTermRecord::where('term_id', $term->id)
                ->whereIn('student_id', $students->pluck('id'))
                ->get()
                ->keyBy('student_id');
            
            // Aggregate data
            $reports = [];
            $subjectIds = $subjects->pluck('id')->toArray();
            foreach ($students as $student) {
                $studentMarks = $allMarks->get($student->id, collect())
                    ->filter(fn($m) => in_array($m->subject_id, $subjectIds));
                
                $marks = $studentMarks->pluck('score', 'subject_id');
                $total = $marks->sum();
                $count = $marks->count();
                $average = $count > 0 ? $total / $count : 0;
                
                $record = $termRecords->get($student->id);

                $reports[] = [
                    'student' => $student,
                    'marks' => $marks,
                    'total' => $total,
                    'average' => $average,
                    'gender' => $student->gender,
                    'conduct' => $record->conduct_grade ?? '',
                    'absence' => $record->days_absent ?? '',
                ];
            }

            // Calculate Rank based on Total Score
            usort($reports, function ($a, $b) {
                return $b['total'] <=> $a['total'];
            });

            $currentRank = 1;
            $prevTotal = null;
            $actualRank = 1;
            foreach ($reports as $index => &$report) {
                if ($prevTotal !== null && $report['total'] < $prevTotal) {
                    $actualRank = $index + 1;
                }
                $report['rank'] = $actualRank;
                $prevTotal = $report['total'];
            }

            // Re-sort by name for display (default for Roster)
            usort($reports, function ($a, $b) {
                return strcmp($a['student']->full_name, $b['student']->full_name);
            });
        }

        $reportType = $request->get('report_type', 'roster');

        if ($reportType === 'report_card') {
            return redirect()->route('admin.section-grades.bulk-print-report-cards', [
                'section' => $section->id,
                'academic_year_id' => $academicYear->id,
                'term_id' => $term->id
            ]);
        }

        if ($reportType === 'result_analysis') {
            return view('admin.academic-reports.analysis', compact('academicYear', 'term', 'section', 'subjects', 'reports'));
        }

        $generalSettings = \App\Models\ReportCardSetting::first();

        return view('admin.academic-reports.show', compact('academicYear', 'term', 'section', 'subjects', 'reports', 'settings', 'generalSettings'));
    }
}
