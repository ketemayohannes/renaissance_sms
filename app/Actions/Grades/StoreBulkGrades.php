<?php

namespace App\Actions\Grades;

use App\Models\Term;
use App\Models\Subject;
use App\Models\StudentMark;
use Illuminate\Support\Facades\DB;

class StoreBulkGrades
{
    /**
     * Store bulk grades for a section and subject.
     *
     * @param array $data Expected keys: academic_year_id, term_id, subject_id, section_id, marks
     * @param int $userId The ID of the teacher/user making the entry
     * @return array Returns an array with 'success' boolean and 'message' string, or throws exception.
     * @throws \Exception
     */
    public static function run(array $data, int $userId)
    {
        $term = Term::findOrFail($data['term_id']);
        if (!$term->is_grading_open) {
            $user = \App\Models\User::find($userId);
            if (!$user || !$user->hasRole(['Super Admin', 'Principal'])) {
                throw new \Exception('Grading for this term is currently closed.');
            }
        }
        
        $subject = Subject::findOrFail($data['subject_id']);
        
        // VALIDATION: Check for Elective/Regular vs Term Type conflicts
        if ($subject->is_elective && $term->type === 'quarter') {
        }

        if (!$subject->is_elective && $term->type === 'semester') {
        }

        DB::beginTransaction();
        try {
            // Load all assessment templates for validation
            $templateIds = collect($data['marks'])->flatMap(fn($components) => array_keys($components))->unique();
            $templates = \App\Models\AssessmentTemplate::whereIn('id', $templateIds)->get()->keyBy('id');

            // Load existing marks for comparison
            $existingMarks = StudentMark::where('academic_year_id', $data['academic_year_id'])
                ->where('term_id', $data['term_id'])
                ->where('subject_id', $data['subject_id'])
                ->whereIn('student_id', array_keys($data['marks']))
                ->get()
                ->groupBy('student_id');

            $errors = [];
            $upsertData = [];
            $deleteData = []; // Track scores to delete (cleared by user)
            $changedStudentIds = [];
            $now = now();
            
            foreach ($data['marks'] as $studentId => $components) {
                foreach ($components as $templateId => $markData) {
                    $existingStudentMarks = $existingMarks->get($studentId);
                    $existingMark = $existingStudentMarks ? $existingStudentMarks->firstWhere('assessment_template_id', $templateId) : null;

                    // If score is empty, add to delete list (user cleared the grade)
                    if (!isset($markData['score']) || $markData['score'] === '') {
                        if ($existingMark) {
                            $deleteData[] = [
                                'student_id' => $studentId,
                                'assessment_template_id' => $templateId,
                            ];
                            $changedStudentIds[] = $studentId;
                        }
                        continue;
                    }

                    $newScore = (float)$markData['score'];
                    $newRemarks = $markData['remarks'] ?? null;

                    // Skip database update if no change occurred
                    if ($existingMark) {
                        $existingScore = (float)$existingMark->score;
                        $existingRemarks = $existingMark->remarks;
                        if ($existingScore === $newScore && $existingRemarks === $newRemarks) {
                            continue;
                        }
                    }

                    // Validate score doesn't exceed max_score
                    $template = $templates->get($templateId);
                    if ($template && $newScore > $template->max_score) {
                        $student = \App\Models\Student::find($studentId);
                        $studentName = $student ? $student->first_name . ' ' . $student->last_name : "Student ID $studentId";
                        $errors[] = "$studentName: Score {$newScore} exceeds maximum {$template->max_score} for {$template->name}";
                        continue; // Skip this entry
                    }

                    $upsertData[] = [
                        'student_id' => $studentId,
                        'academic_year_id' => $data['academic_year_id'],
                        'term_id' => $data['term_id'],
                        'subject_id' => $data['subject_id'],
                        'assessment_template_id' => $templateId,
                        'section_id' => $data['section_id'],
                        'teacher_id' => $userId,
                        'score' => $newScore,
                        'remarks' => $newRemarks,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];

                    $changedStudentIds[] = $studentId;
                }
            }
            
            // Delete cleared grades
            foreach ($deleteData as $toDelete) {
                StudentMark::where('student_id', $toDelete['student_id'])
                    ->where('assessment_template_id', $toDelete['assessment_template_id'])
                    ->where('subject_id', $data['subject_id'])
                    ->where('term_id', $data['term_id'])
                    ->where('academic_year_id', $data['academic_year_id'])
                    ->delete();
            }

            if (!empty($errors)) {
                // STRICT MODE: If any error exists, fail the whole batch
                DB::rollBack();
                throw new \Exception('Validation failed. No changes were saved. ' . implode('; ', $errors));
            }

            if (!empty($upsertData)) {
                StudentMark::upsert(
                    $upsertData,
                    ['student_id', 'assessment_template_id', 'subject_id'],
                    ['score', 'remarks', 'teacher_id', 'section_id', 'subject_id', 'updated_at']
                );

                // Manual Audit Injection
                \App\Models\AuditLog::create([
                    'user_id' => $userId,
                    'event' => 'bulk_grade_entry',
                    'auditable_type' => StudentMark::class,
                    'auditable_id' => $data['subject_id'],
                    'section_id' => $data['section_id'],
                    'new_values' => ['subject_id' => $data['subject_id'], 'term_id' => $data['term_id'], 'section_id' => $data['section_id'], 'count' => count($upsertData)],
                    'url' => request()->fullUrl(),
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
            }

            // SYNCHRONIZATION: Update the TERM_TOTAL mark for all affected students
            // This ensures the Master Sheet reflects the changes made in the Gradebook.
            $termTotalTypeId = \App\Models\AssessmentType::where('code', 'TERM_TOTAL')->value('id');
            if ($termTotalTypeId && !empty($changedStudentIds)) {
                // Get the grade level from the section
                $section = \App\Models\Section::find($data['section_id']);
                $gradeLevelId = $section ? $section->grade_level_id : null;

                $termTotalTemplate = \App\Models\AssessmentTemplate::where('academic_year_id', $data['academic_year_id'])
                    ->where('term_id', $data['term_id'])
                    ->where('assessment_type_id', $termTotalTypeId)
                    ->whereHas('assignments', function($q) use ($data, $gradeLevelId) {
                        $q->where('subject_id', $data['subject_id']);
                        if ($gradeLevelId) {
                            $q->where('grade_level_id', $gradeLevelId);
                        }
                    })
                    ->first();

                if ($termTotalTemplate) {
                    $affectedStudentIds = array_unique($changedStudentIds);
                    foreach ($affectedStudentIds as $studentId) {
                        // Calculate new total from ALL current components in DB
                        $newSum = StudentMark::where('student_id', $studentId)
                            ->where('academic_year_id', $data['academic_year_id'])
                            ->where('term_id', $data['term_id'])
                            ->where('subject_id', $data['subject_id'])
                            ->where('assessment_template_id', '!=', $termTotalTemplate->id)
                            ->sum('score');

                        if ($newSum > 0) {
                            StudentMark::updateOrCreate(
                                [
                                    'student_id' => $studentId,
                                    'academic_year_id' => $data['academic_year_id'],
                                    'term_id' => $data['term_id'],
                                    'subject_id' => $data['subject_id'],
                                    'assessment_template_id' => $termTotalTemplate->id,
                                ],
                                [
                                    'section_id' => $data['section_id'],
                                    'teacher_id' => $userId,
                                    'score' => $newSum,
                                    'remarks' => 'Auto-calculated from components'
                                ]
                            );
                        } else {
                            // If sum is 0, delete the Term Total mark to avoid stale data
                            StudentMark::where([
                                'student_id' => $studentId,
                                'academic_year_id' => $data['academic_year_id'],
                                'term_id' => $data['term_id'],
                                'subject_id' => $data['subject_id'],
                                'assessment_template_id' => $termTotalTemplate->id,
                            ])->delete();
                        }
                    }
                }
            }
            
            DB::commit();
            
            // Trigger recalculation to update TERM_TOTAL and section statistics
            try {
                $section = \App\Models\Section::find($data['section_id']);
                if ($section) {
                    $academicYear = \App\Models\AcademicYear::find($data['academic_year_id']);
                    $gradingService = app(\App\Services\GradingService::class);
                    $gradingService->recalculateSectionStatistics($section, $term, $academicYear);
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Background recalculation failed in StoreBulkGrades: ' . $e->getMessage());
            }

            return ['success' => true, 'message' => 'Marks saved successfully.'];
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
