<?php

namespace App\Actions\Grades;

use App\Models\AcademicYear;
use App\Models\AssessmentTemplate;
use App\Models\AssessmentType;
use App\Models\AuditLog;
use App\Models\Section;
use App\Models\StudentMark;
use App\Models\Subject;
use App\Models\Term;
use App\Models\User;
use App\Services\AcademicReportService;
use App\Services\GradingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImportGradesFromCsv
{
    /**
     * Import component marks for one section + subject + term from a gradebook CSV
     * (the file produced by "Export Template"). Shared by the admin and teacher gradebooks.
     *
     * Rows are rejected (and reported) when the student is not enrolled in this section,
     * when the name column does not match the student ID (a re-sorted spreadsheet), or
     * when a score is not a number or exceeds the template's maximum.
     *
     * @return array{type: 'success'|'warning'|'error', message: string}
     */
    public static function run(string $path, Section $section, Subject $subject, Term $term, int $academicYearId, int $userId): array
    {
        if (! $term->is_grading_open) {
            $user = User::find($userId);
            if (! $user || ! $user->hasRole(['Super Admin', 'Principal'])) {
                return ['type' => 'error', 'message' => 'Grading for this term is currently closed.'];
            }
        }

        // Fetch assessment templates (excluding TERM_TOTAL - it's a calculated value)
        $termTotalType = AssessmentType::where('code', 'TERM_TOTAL')->first();

        $templates = AssessmentTemplate::forGradeSubject($section->grade_level_id, $subject->id)
            ->where('academic_year_id', $academicYearId)
            ->where('term_id', $term->id)
            ->where('is_active', true)
            ->when($termTotalType, function ($query) use ($termTotalType) {
                $query->where('assessment_type_id', '!=', $termTotalType->id);
            })
            ->get();

        $file = fopen($path, 'r');

        try {
            // Skip BOM if present
            $bom = fread($file, 3);
            if ($bom !== "\xEF\xBB\xBF") {
                rewind($file);
            }

            // Read first line to check for sep=,
            $line = fgets($file);
            $headerLine = ($line !== false && str_starts_with($line, 'sep=')) ? fgets($file) : $line;

            if (! $headerLine || trim($headerLine) === '') {
                return ['type' => 'error', 'message' => 'The uploaded file is empty or invalid.'];
            }

            // Auto-detect delimiter
            $delimiter = ',';
            $maxCount = 0;
            foreach ([',', ';', "\t"] as $d) {
                $count = substr_count($headerLine, $d);
                if ($count > $maxCount) {
                    $maxCount = $count;
                    $delimiter = $d;
                }
            }

            // Map CSV columns ("Name (Max: 10)" or just "Name") to templates; skip ID and Name
            $columnMap = [];
            foreach (str_getcsv($headerLine, $delimiter) as $index => $colName) {
                if ($index < 2) {
                    continue;
                }
                $name = trim(preg_replace('/\s*\(Max:.*\)/', '', $colName));
                $template = $templates->first(fn ($t) => strtolower($t->name) === strtolower($name));
                if ($template) {
                    $columnMap[$index] = $template;
                }
            }

            // Only students enrolled in this section can receive marks here.
            $sectionStudents = $section->students()->get()->keyBy('student_id');

            $successCount = 0;
            $errors = [];
            $upsertData = [];
            $now = now();

            while (($line = fgets($file)) !== false) {
                $row = str_getcsv($line, $delimiter);

                // Skip empty rows
                if (empty($row) || (count($row) === 1 && empty($row[0]))) {
                    continue;
                }

                $studentIdStr = trim($row[0]);
                $csvName = isset($row[1]) ? trim($row[1]) : '';
                $student = $sectionStudents->get($studentIdStr);

                if (! $student) {
                    $errors[] = "Student ID $studentIdStr not found in this section";

                    continue;
                }

                // Strict name validation to catch Excel sorting errors
                $dbName = $student->full_name;
                $similarity = 0;
                similar_text(strtolower($csvName), strtolower($dbName), $similarity);
                if ($similarity < 70 && ! str_contains(strtolower($dbName), strtolower(explode(' ', $csvName)[0]))) {
                    $errors[] = "CRITICAL MISMATCH: ID $studentIdStr belongs to '$dbName', but CSV has '$csvName'. The spreadsheet may have been sorted incorrectly.";

                    continue;
                }

                foreach ($columnMap as $index => $template) {
                    $score = trim($row[$index] ?? '');
                    if ($score === '') {
                        continue;
                    }

                    if (! is_numeric($score)) {
                        $errors[] = "Invalid score for $studentIdStr in {$template->name}";

                        continue;
                    }

                    if ($score > $template->max_score) {
                        $errors[] = "Score $score exceeds max {$template->max_score} for $studentIdStr in {$template->name}";

                        continue;
                    }

                    $upsertData[] = [
                        'student_id' => $student->id,
                        'academic_year_id' => $academicYearId,
                        'term_id' => $term->id,
                        'subject_id' => $subject->id,
                        'assessment_template_id' => $template->id,
                        'section_id' => $section->id,
                        'teacher_id' => $userId,
                        'score' => $score,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
                $successCount++;
            }
        } finally {
            fclose($file);
        }

        if (! empty($upsertData)) {
            DB::transaction(function () use ($upsertData, $userId, $subject, $section, $term) {
                StudentMark::upsert(
                    $upsertData,
                    ['student_id', 'assessment_template_id'],
                    ['score', 'teacher_id', 'section_id', 'updated_at']
                );

                // Manual audit entry: upsert() bypasses the Auditable model events
                AuditLog::create([
                    'user_id' => $userId,
                    'event' => 'bulk_grade_import',
                    'auditable_type' => StudentMark::class,
                    'auditable_id' => $subject->id,
                    'section_id' => $section->id,
                    'new_values' => ['subject_id' => $subject->id, 'term_id' => $term->id, 'section_id' => $section->id, 'count' => count($upsertData)],
                    'url' => request()->fullUrl(),
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
            });

            // A failed recalculation must not report the (already saved) import as failed.
            try {
                app(GradingService::class)->recalculateSectionStatistics($section, $term, AcademicYear::find($academicYearId));
            } catch (\Exception $e) {
                Log::error('Recalculation failed after Gradebook import: '.$e->getMessage());
            }

            // upsert() skips the StudentMark model events that normally bust the roster cache.
            AcademicReportService::clearRosterCache($section->id, $term->id, $academicYearId);
        }

        $errorList = empty($errors) ? '' : ' Errors: '.implode('; ', array_slice($errors, 0, 5)).(count($errors) > 5 ? '...' : '');

        if (empty($upsertData)) {
            $reason = $successCount === 0 && empty($errors)
                ? ' The file appears to be empty or contains no valid data.'
                : ($successCount > 0 && empty($errors) ? " All score fields were empty for $successCount students." : '');

            return ['type' => 'warning', 'message' => 'No grades were imported.'.$reason.$errorList];
        }

        $message = 'Imported '.count($upsertData)." grades for $successCount students.";

        return empty($errors)
            ? ['type' => 'success', 'message' => $message]
            : ['type' => 'warning', 'message' => $message.$errorList];
    }
}
