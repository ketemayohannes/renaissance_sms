<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notice;

class DashboardController extends Controller
{
    public function index()
    {
        // Get linked students for the authenticated parent
        $user = auth()->user();
        $children = $user->linked_students->load([
            'currentEnrollment.section.gradeLevel',
            'marks.subject',
            'marks.assessmentTemplate'
        ]);

        $academicYear = \App\Helpers\CachedData::activeAcademicYear();
        $quarters = collect();
        if ($academicYear) {
            $quarters = \App\Models\Term::where('academic_year_id', $academicYear->id)
                ->where('type', 'quarter')
                ->orderBy('term_number', 'asc')
                ->get();
        }

        // Compute performance summaries and alerts for each child
        $children->each(function ($child) use ($academicYear, $quarters) {
            // Attendance Rate
            $attendanceCount = $child->attendance()->count();
            $presentCount = $child->attendance()->whereIn('status', ['present', 'late', 'Present', 'Late'])->count();
            $child->attendance_rate = $attendanceCount > 0 ? round(($presentCount / $attendanceCount) * 100, 1) : 100;

            // Average Score (Nearly Closed Term Average)
            $averageScore = null;
            $nearlyClosedTermName = '';
            $defaultTermId = '';
            $alertsByTerm = [];
            
            if ($academicYear && $quarters->isNotEmpty()) {
                $today = now();
                // Find the last quarter that has started
                $currentQuarter = $quarters->filter(fn($q) => $q->start_date <= $today)->last();
                
                if (!$currentQuarter) {
                    $currentQuarter = $quarters->first();
                }
                
                $currentIndex = $quarters->search(fn($q) => $q->id === $currentQuarter->id);
                if ($currentIndex > 0) {
                    // Previous term is the nearly closed term
                    $nearlyClosedTerm = $quarters->get($currentIndex - 1);
                } else {
                    // If we are in Quarter 1, fallback to Quarter 1
                    $nearlyClosedTerm = $currentQuarter;
                }
                
                if ($nearlyClosedTerm) {
                    $nearlyClosedTermName = $nearlyClosedTerm->name;
                    $defaultTermId = $nearlyClosedTerm->id;
                    
                    // Try to get the calculated average and rank from StudentTermRecord first
                    $termRecord = \App\Models\StudentTermRecord::where('student_id', $child->id)
                        ->where('term_id', $nearlyClosedTerm->id)
                        ->first();
                    
                    $nearlyClosedTermRank = null;
                    if ($termRecord) {
                        if ($termRecord->average_score !== null) {
                            $averageScore = $termRecord->average_score;
                        }
                        if ($termRecord->rank !== null) {
                            $nearlyClosedTermRank = $termRecord->rank;
                        }
                    } else {
                        // Calculate dynamically via GradingService
                        $gradingService = app(\App\Services\GradingService::class);
                        $stats = $gradingService->calculateStudentTotals($child, $nearlyClosedTerm, $academicYear);
                        $averageScore = $stats['average'] ?? null;
                    }
                } else {
                    $defaultTermId = $quarters->first()->id;
                }
                
                // Pre-calculate warning/critical subject alerts by term
                $termTotalTypeId = \App\Models\AssessmentType::where('code', 'TERM_TOTAL')->value('id') 
                    ?? \App\Models\AssessmentType::firstOrCreate(
                        ['code' => 'TERM_TOTAL'],
                        ['name' => 'Term Total', 'weight' => 100, 'max_score' => 100, 'is_active' => true]
                    )->id;
                
                $childMarks = $child->marks;
                
                foreach ($quarters as $quarter) {
                    $alertsByTerm[$quarter->id] = [
                        'term_name' => $quarter->name,
                        'warning' => [],
                        'critical' => [],
                    ];
                    
                    $quarterMarks = $childMarks->where('term_id', $quarter->id);
                    if ($quarterMarks->isNotEmpty()) {
                        foreach ($quarterMarks->groupBy('subject_id') as $subjectId => $subjectMarks) {
                            $subject = $subjectMarks->first()->subject;
                            if (!$subject) continue;
                            
                            // Components-first: prefer live component marks; fall back to the
                            // TERM_TOTAL row only when no components exist. This keeps parent
                            // low-performance alerts in sync with corrected component grades.
                            $components = $subjectMarks->filter(fn($m) => !$m->assessmentTemplate || $m->assessmentTemplate->assessment_type_id != $termTotalTypeId);
                            if ($components->isNotEmpty()) {
                                $score = $components->sum('score');
                            } else {
                                $termTotal = $subjectMarks->first(fn($m) => $m->assessmentTemplate && $m->assessmentTemplate->assessment_type_id == $termTotalTypeId);
                                $score = $termTotal ? $termTotal->score : 0;
                            }

                            if ($score <= 50) {
                                $alertsByTerm[$quarter->id]['critical'][] = [
                                    'subject_name' => $subject->name,
                                    'score' => round($score, 1),
                                ];
                            } elseif ($score < 75) {
                                $alertsByTerm[$quarter->id]['warning'][] = [
                                    'subject_name' => $subject->name,
                                    'score' => round($score, 1),
                                ];
                            }
                        }
                    }
                }
            }
            
            $child->average_score = $averageScore !== null ? round($averageScore, 1) : null;
            $child->nearly_closed_term_name = $nearlyClosedTermName;
            $child->nearly_closed_term_rank = $nearlyClosedTermRank ?? null;
            $child->default_term_id = $defaultTermId;
            $child->alerts_by_term = $alertsByTerm;

            // Conduct Incident Count
            $child->conduct_incidents_count = $child->disciplinaryRecords()->count();
        });

        // Active notices targeting parents or all audiences
        $notices = Notice::active()
            ->whereIn('target_audience', ['Parent', 'All'])
            ->orderByDesc('publish_date')
            ->take(5)
            ->get();

        return view('parent.dashboard', [
            'children' => $children,
            'quarters' => $quarters,
            'notices' => $notices,
        ]);
    }
}
