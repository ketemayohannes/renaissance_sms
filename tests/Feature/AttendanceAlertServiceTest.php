<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Division;
use App\Models\GradeLevel;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\User;
use App\Services\AttendanceAlertService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceAlertServiceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_flags_only_students_with_three_consecutive_absences(): void
    {
        $year = AcademicYear::factory()->active()->create();
        $division = Division::factory()->create();
        $gradeLevel = GradeLevel::factory()->create(['division_id' => $division->id]);
        $section = Section::factory()->create([
            'grade_level_id' => $gradeLevel->id,
            'academic_year_id' => $year->id,
        ]);

        $marker = User::factory()->create();
        $atRisk = Student::factory()->create();     // absent 3 days running
        $recovered = Student::factory()->create();  // absent, absent, then present

        foreach ([$atRisk, $recovered] as $student) {
            $student->sections()->attach($section->id, [
                'academic_year_id' => $year->id,
                'enrollment_date' => now(),
                'status' => 'active',
            ]);
        }

        // Three consecutive school days (newest last).
        $days = [now()->subDays(2), now()->subDay(), now()];
        $statuses = [
            $atRisk->id    => ['absent', 'absent', 'absent'],
            $recovered->id => ['absent', 'absent', 'present'],
        ];

        foreach ($statuses as $studentId => $dayStatuses) {
            foreach ($days as $i => $day) {
                StudentAttendance::create([
                    'student_id' => $studentId,
                    'section_id' => $section->id,
                    'academic_year_id' => $year->id,
                    'attendance_date' => $day->format('Y-m-d'),
                    'status' => $dayStatuses[$i],
                    'marked_by' => $marker->id,
                ]);
            }
        }

        $result = (new AttendanceAlertService())->getAtRiskStudents($section);

        $ids = $result->pluck('id');
        $this->assertTrue($ids->contains($atRisk->id), 'Student with 3 consecutive absences should be flagged.');
        $this->assertFalse($ids->contains($recovered->id), 'Student whose latest day is present should not be flagged.');
    }
}
