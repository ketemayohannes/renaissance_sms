<?php

namespace App\Services;

use App\Models\AcademicActivity;
use App\Models\ActivitySubmission;
use App\Models\StudentMark;
use App\Models\ActivityAttachment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AcademicActivityService
{
    /**
     * Create a new academic activity.
     */
    public function createActivity(array $data)
    {
        return DB::transaction(function () use ($data) {
            $activity = AcademicActivity::create($data);

            // If there are attachments (e.g., homework files)
            if (isset($data['attachments'])) {
                foreach ($data['attachments'] as $file) {
                    $path = $file->store('activities/' . $activity->id, 'public');
                    $activity->attachments()->create([
                        'file_path' => $path,
                        'file_name' => $file->getClientOriginalName(),
                        'file_type' => $file->getMimeType(),
                    ]);
                }
            }

            return $activity;
        });
    }

    /**
     * Save/Update exam questions for an activity.
     */
    public function saveExamQuestions(AcademicActivity $activity, array $questions)
    {
        return DB::transaction(function () use ($activity, $questions) {
            $activity->questions()->delete(); // Simple replace for now
            return $activity->questions()->createMany($questions);
        });
    }

    /**
     * Submit work for an activity.
     */
    public function submitWork(AcademicActivity $activity, $studentId, array $data)
    {
        return DB::transaction(function () use ($activity, $studentId, $data) {
            $submission = ActivitySubmission::updateOrCreate(
                ['academic_activity_id' => $activity->id, 'student_id' => $studentId],
                [
                    'submitted_at' => now(),
                    'status' => 'submitted',
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]
            );

            // Handle Attachments (Digital Dossier Pattern)
            if (isset($data['attachments'])) {
                foreach ($data['attachments'] as $file) {
                    $path = $file->store('submissions/' . $submission->id, 'public');
                    $submission->attachments()->create([
                        'file_path' => $path,
                        'file_name' => $file->getClientOriginalName(),
                        'file_type' => $file->getMimeType(),
                    ]);
                }
            }

            return $submission;
        });
    }

    /**
     * Grade a submission and sync with StudentMark.
     */
    public function evaluateSubmission(ActivitySubmission $submission, array $data)
    {
        return DB::transaction(function () use ($submission, $data) {
            $submission->update([
                'score' => $data['score'],
                'feedback' => $data['feedback'] ?? null,
                'status' => 'graded',
                'graded_by' => auth()->id(),
                'graded_at' => now(),
            ]);

            $activity = $submission->activity;

            // Sync with StudentMark if linked to a template
            if ($activity->assessment_template_id) {
                $this->syncMarkToGradebook($submission);
            }

            return $submission;
        });
    }

    /**
     * Sync the submission score to the official StudentMark table.
     */
    protected function syncMarkToGradebook(ActivitySubmission $submission)
    {
        $activity = $submission->activity;
        $assignment = $activity->teacherAssignment;

        // Scale the score to match the template's max_score if they differ
        $score = $submission->score;
        if ($activity->max_score != $activity->assessmentTemplate->max_score && $activity->max_score > 0) {
            $score = ($submission->score / $activity->max_score) * $activity->assessmentTemplate->max_score;
        }

        StudentMark::updateOrCreate(
            [
                'student_id' => $submission->student_id,
                'assessment_template_id' => $activity->assessment_template_id,
                'academic_year_id' => $activity->academic_year_id,
                'term_id' => $activity->assessmentTemplate->term_id,
                'subject_id' => $assignment->subject_id,
            ],
            [
                'section_id' => $assignment->section_id,
                'teacher_id' => $assignment->teacher_id,
                'score' => $score,
                'remarks' => 'Synced from Academic Activity: ' . $activity->title,
            ]
        );
    }
}
