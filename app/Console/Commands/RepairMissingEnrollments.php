<?php

namespace App\Console\Commands;

use App\Models\StudentEnrollment;
use App\Models\StudentPromotion;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RepairMissingEnrollments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:repair-missing-enrollments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backfill enrollment records for promotions marked enrolled before section_id was made nullable, so their next-year enrollment (Unassigned if no section exists yet) actually exists';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $promotions = StudentPromotion::with('toAcademicYear')
            ->where('is_enrolled', true)
            ->where('status', '!=', 'graduated')
            ->get()
            ->filter(function (StudentPromotion $promo) {
                if (! $promo->toAcademicYear) {
                    return false;
                }

                return ! StudentEnrollment::where('student_id', $promo->student_id)
                    ->where('academic_year_id', $promo->to_academic_year_id)
                    ->exists();
            });

        if ($promotions->isEmpty()) {
            $this->info('No missing enrollments found.');

            return self::SUCCESS;
        }

        $this->info("Found {$promotions->count()} promotion(s) missing their next-year enrollment. Repairing...");

        $sectionsToRecalculate = [];

        DB::transaction(function () use ($promotions, &$sectionsToRecalculate) {
            foreach ($promotions as $promo) {
                $nextAcademicYear = $promo->toAcademicYear;

                StudentEnrollment::create([
                    'student_id' => $promo->student_id,
                    'academic_year_id' => $nextAcademicYear->id,
                    'section_id' => $promo->to_section_id,
                    'enrollment_date' => $nextAcademicYear->start_date,
                    'status' => 'active',
                ]);

                if ($promo->to_section_id) {
                    $sectionsToRecalculate[$promo->to_section_id] = $nextAcademicYear->id;
                }
            }

            foreach ($sectionsToRecalculate as $sectionId => $academicYearId) {
                StudentEnrollment::recalculateRollNumbers($sectionId, $academicYearId);
            }
        });

        $this->info("Repaired {$promotions->count()} enrollment(s).");

        return self::SUCCESS;
    }
}
