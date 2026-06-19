<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\DisciplinaryRecord;
use App\Models\EscalationRule;
use App\Models\InfractionDefinition;
use App\Models\Student;
use App\Models\User;
use App\Services\DisciplineEscalationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DisciplinaryModuleTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected AcademicYear $academicYear;
    protected Student $student;

    protected function setUp(): void
    {
        parent::setUp();

        // Create admin user with role
        Role::firstOrCreate(['name' => 'Super Admin']);
        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole('Super Admin');

        $this->academicYear = AcademicYear::where('is_active', true)->first()
            ?? AcademicYear::factory()->active()->create();
        $this->student = Student::factory()->create();

        // Clear any pre-seeded disciplinary records, rules, and definitions
        // to isolate the test from the database's initial seeded state.
        // These deletions will be rolled back by the RefreshDatabase trait transaction.
        DisciplinaryRecord::query()->delete();
        EscalationRule::query()->delete();
        InfractionDefinition::query()->delete();
    }

    /** @test */
    public function can_create_and_manage_infraction_definitions()
    {
        $this->actingAs($this->adminUser);

        // Create infraction definition via controller/route if available, or test model directly
        $infraction = InfractionDefinition::create([
            'name' => 'Cheating on Test',
            'code' => 'cheating_on_test',
            'tier' => 'moderate',
            'description' => 'Cheating during an exam',
            'default_penalty' => 'Zero on test and written warning',
            'requires_parent_notification' => true,
            'display_order' => 1,
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('infraction_definitions', [
            'name' => 'Cheating on Test',
            'code' => 'cheating_on_test',
            'tier' => 'moderate',
        ]);

        $this->assertEquals('Moderate', $infraction->display_tier);
    }

    /** @test */
    public function service_evaluates_escalation_rules_and_applies_correct_action()
    {
        // 1. Create infraction definition
        $infraction = InfractionDefinition::create([
            'name' => 'Lateness',
            'code' => 'lateness',
            'tier' => 'minor',
            'description' => 'Arriving late to class',
            'default_penalty' => 'Verbal Warning',
            'requires_parent_notification' => false,
            'display_order' => 1,
            'is_active' => true,
        ]);

        // 2. Create escalation rules
        $rule = EscalationRule::create([
            'infraction_definition_id' => null, // applies to all minor infractions
            'tier' => 'minor',
            'occurrence_threshold' => 3,
            'time_window_days' => 30,
            'escalation_action' => 'written_warning',
            'escalation_description' => '3 latenesses in 30 days results in written warning',
            'auto_notify_parent' => true,
            'is_active' => true,
        ]);

        $service = new DisciplineEscalationService();

        // Create first record - should not trigger escalation
        $record1 = DisciplinaryRecord::create([
            'student_id' => $this->student->id,
            'academic_year_id' => $this->academicYear->id,
            'infraction_definition_id' => $infraction->id,
            'incident_date' => now(),
            'description' => 'Late 10 mins',
            'reported_by' => $this->adminUser->id,
            'status' => 'reported',
            'notify_parent' => false,
        ]);

        $triggeredRule1 = $service->evaluateEscalation($record1);
        $this->assertNull($triggeredRule1);
        $this->assertNull($record1->fresh()->escalation_rule_id);
        $this->assertFalse($record1->fresh()->notify_parent);

        // Create second record - should not trigger escalation
        $record2 = DisciplinaryRecord::create([
            'student_id' => $this->student->id,
            'academic_year_id' => $this->academicYear->id,
            'infraction_definition_id' => $infraction->id,
            'incident_date' => now(),
            'description' => 'Late 15 mins',
            'reported_by' => $this->adminUser->id,
            'status' => 'reported',
            'notify_parent' => false,
        ]);

        $triggeredRule2 = $service->evaluateEscalation($record2);
        $this->assertNull($triggeredRule2);

        // Create third record - should trigger escalation
        $record3 = DisciplinaryRecord::create([
            'student_id' => $this->student->id,
            'academic_year_id' => $this->academicYear->id,
            'infraction_definition_id' => $infraction->id,
            'incident_date' => now(),
            'description' => 'Late 20 mins',
            'reported_by' => $this->adminUser->id,
            'status' => 'reported',
            'notify_parent' => false,
        ]);

        $triggeredRule3 = $service->evaluateEscalation($record3);
        $this->assertNotNull($triggeredRule3);
        $this->assertEquals($rule->id, $triggeredRule3->id);
        $this->assertEquals('written_warning', $record3->fresh()->escalation_action_applied);
        $this->assertTrue($record3->fresh()->notify_parent);
    }

    /** @test */
    public function critical_incidents_escalate_on_first_occurrence()
    {
        $infraction = InfractionDefinition::create([
            'name' => 'Physical Assault',
            'code' => 'physical_assault',
            'tier' => 'critical',
            'description' => 'Fighting or assault',
            'default_penalty' => 'Suspension',
            'requires_parent_notification' => true,
            'display_order' => 1,
            'is_active' => true,
        ]);

        $rule = EscalationRule::create([
            'infraction_definition_id' => null,
            'tier' => 'critical',
            'occurrence_threshold' => 1,
            'time_window_days' => null,
            'escalation_action' => 'suspension',
            'escalation_description' => 'First critical incident triggers immediate suspension',
            'auto_notify_parent' => true,
            'is_active' => true,
        ]);

        $record = DisciplinaryRecord::create([
            'student_id' => $this->student->id,
            'academic_year_id' => $this->academicYear->id,
            'infraction_definition_id' => $infraction->id,
            'incident_date' => now(),
            'description' => 'Involved in a fight',
            'reported_by' => $this->adminUser->id,
            'status' => 'reported',
            'notify_parent' => true,
        ]);

        $service = new DisciplineEscalationService();
        $triggeredRule = $service->evaluateEscalation($record);

        $this->assertNotNull($triggeredRule);
        $this->assertEquals($rule->id, $triggeredRule->id);
        $this->assertEquals('suspension', $record->fresh()->escalation_action_applied);
        $this->assertEquals('escalated', $record->fresh()->status);
    }

    /** @test */
    public function admin_can_access_disciplinary_settings()
    {
        $this->actingAs($this->adminUser);

        $response = $this->get(route('admin.discipline-settings.index'));
        $response->assertStatus(200);
    }
}
