<?php

namespace Database\Seeders;

use App\Models\EscalationRule;
use App\Models\InfractionDefinition;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class InfractionDefinitionSeeder extends Seeder
{
    /**
     * Seed the infraction definitions and escalation rules based on
     * Addis Ababa City Administration School Directive No. 150/2016.
     */
    public function run(): void
    {
        // ── MINOR INFRACTIONS ──────────────────────────────────
        $minor = [
            [
                'name'                         => 'Tardiness / Late Arrival',
                'description'                  => 'Student arrives late to class or school without a valid excuse.',
                'default_penalty'              => 'Verbal Warning',
                'requires_parent_notification' => false,
                'display_order'                => 1,
            ],
            [
                'name'                         => 'Incomplete Homework',
                'description'                  => 'Student repeatedly fails to complete or submit assigned homework.',
                'default_penalty'              => 'Written Warning',
                'requires_parent_notification' => false,
                'display_order'                => 2,
            ],
            [
                'name'                         => 'Disruptive Behavior in Class',
                'description'                  => 'Student causes minor disruptions during lessons (talking, noise).',
                'default_penalty'              => 'Verbal Warning',
                'requires_parent_notification' => false,
                'display_order'                => 3,
            ],
            [
                'name'                         => 'Uniform Violation',
                'description'                  => 'Student fails to comply with the school uniform or dress code policy.',
                'default_penalty'              => 'Verbal Warning',
                'requires_parent_notification' => false,
                'display_order'                => 4,
            ],
        ];

        foreach ($minor as $item) {
            InfractionDefinition::create([
                'name'                         => $item['name'],
                'code'                         => Str::slug($item['name'], '_'),
                'tier'                         => 'minor',
                'description'                  => $item['description'],
                'default_penalty'              => $item['default_penalty'],
                'requires_parent_notification' => $item['requires_parent_notification'],
                'display_order'                => $item['display_order'],
                'is_active'                    => true,
            ]);
        }

        // ── MODERATE INFRACTIONS ───────────────────────────────
        $moderate = [
            [
                'name'                         => 'Academic Dishonesty / Cheating',
                'description'                  => 'Student engages in cheating, plagiarism, or unauthorized assistance during assessments.',
                'default_penalty'              => 'Written Warning & Parent Conference',
                'requires_parent_notification' => true,
                'display_order'                => 1,
            ],
            [
                'name'                         => 'Bullying or Harassment',
                'description'                  => 'Student engages in verbal, social, or cyber bullying of peers.',
                'default_penalty'              => 'Parent Conference & Community Service',
                'requires_parent_notification' => true,
                'display_order'                => 2,
            ],
            [
                'name'                         => 'Vandalism of School Property',
                'description'                  => 'Student deliberately damages or defaces school property.',
                'default_penalty'              => 'Restitution & Detention',
                'requires_parent_notification' => true,
                'display_order'                => 3,
            ],
            [
                'name'                         => 'Repeated Truancy / Unexplained Absence',
                'description'                  => 'Student accumulates multiple unexplained absences within an academic period.',
                'default_penalty'              => 'Parent Conference',
                'requires_parent_notification' => true,
                'display_order'                => 4,
            ],
        ];

        foreach ($moderate as $item) {
            InfractionDefinition::create([
                'name'                         => $item['name'],
                'code'                         => Str::slug($item['name'], '_'),
                'tier'                         => 'moderate',
                'description'                  => $item['description'],
                'default_penalty'              => $item['default_penalty'],
                'requires_parent_notification' => $item['requires_parent_notification'],
                'display_order'                => $item['display_order'],
                'is_active'                    => true,
            ]);
        }

        // ── CRITICAL INFRACTIONS ───────────────────────────────
        $critical = [
            [
                'name'                         => 'Physical Violence / Fighting',
                'description'                  => 'Student physically assaults or fights with another student or staff member.',
                'default_penalty'              => 'Suspension & Mandatory Parent Conference',
                'requires_parent_notification' => true,
                'display_order'                => 1,
            ],
            [
                'name'                         => 'Possession of Prohibited Items',
                'description'                  => 'Student found in possession of weapons, drugs, alcohol, or other prohibited materials.',
                'default_penalty'              => 'Suspension & Police Referral',
                'requires_parent_notification' => true,
                'display_order'                => 2,
            ],
            [
                'name'                         => 'Gross Insubordination',
                'description'                  => 'Student openly defies or threatens a teacher or school administrator.',
                'default_penalty'              => 'Suspension',
                'requires_parent_notification' => true,
                'display_order'                => 3,
            ],
        ];

        foreach ($critical as $item) {
            InfractionDefinition::create([
                'name'                         => $item['name'],
                'code'                         => Str::slug($item['name'], '_'),
                'tier'                         => 'critical',
                'description'                  => $item['description'],
                'default_penalty'              => $item['default_penalty'],
                'requires_parent_notification' => $item['requires_parent_notification'],
                'display_order'                => $item['display_order'],
                'is_active'                    => true,
            ]);
        }

        // ── ESCALATION RULES (Directive 150/2016) ──────────────

        // Minor: 3 incidents in 30 days → Written Warning
        EscalationRule::create([
            'infraction_definition_id' => null, // applies to all minor
            'tier'                     => 'minor',
            'occurrence_threshold'     => 3,
            'time_window_days'         => 30,
            'escalation_action'        => 'written_warning',
            'escalation_description'   => 'Three or more minor infractions within 30 days triggers a formal written warning.',
            'auto_notify_parent'       => true,
            'is_active'                => true,
            'legal_reference'          => 'Directive 150/2016, Art. 4(1)',
        ]);

        // Minor: 5 incidents in academic year → Parent Conference
        EscalationRule::create([
            'infraction_definition_id' => null,
            'tier'                     => 'minor',
            'occurrence_threshold'     => 5,
            'time_window_days'         => null, // per academic year
            'escalation_action'        => 'parent_conference',
            'escalation_description'   => 'Five or more minor infractions in the academic year requires a mandatory parent conference.',
            'auto_notify_parent'       => true,
            'is_active'                => true,
            'legal_reference'          => 'Directive 150/2016, Art. 4(2)',
        ]);

        // Moderate: 2 incidents in 60 days → Detention
        EscalationRule::create([
            'infraction_definition_id' => null,
            'tier'                     => 'moderate',
            'occurrence_threshold'     => 2,
            'time_window_days'         => 60,
            'escalation_action'        => 'detention',
            'escalation_description'   => 'Two or more moderate infractions within 60 days triggers formal detention.',
            'auto_notify_parent'       => true,
            'is_active'                => true,
            'legal_reference'          => 'Directive 150/2016, Art. 5(1)',
        ]);

        // Moderate: 3 incidents in academic year → Suspension
        EscalationRule::create([
            'infraction_definition_id' => null,
            'tier'                     => 'moderate',
            'occurrence_threshold'     => 3,
            'time_window_days'         => null,
            'escalation_action'        => 'suspension',
            'escalation_description'   => 'Three or more moderate infractions in the academic year results in suspension.',
            'auto_notify_parent'       => true,
            'is_active'                => true,
            'legal_reference'          => 'Directive 150/2016, Art. 5(2)',
        ]);

        // Critical: 1st occurrence → Suspension
        EscalationRule::create([
            'infraction_definition_id' => null,
            'tier'                     => 'critical',
            'occurrence_threshold'     => 1,
            'time_window_days'         => null,
            'escalation_action'        => 'suspension',
            'escalation_description'   => 'Any critical infraction immediately triggers suspension pending investigation.',
            'auto_notify_parent'       => true,
            'is_active'                => true,
            'legal_reference'          => 'Directive 150/2016, Art. 6(1)',
        ]);

        // Critical: 2nd occurrence → Expulsion Referral
        EscalationRule::create([
            'infraction_definition_id' => null,
            'tier'                     => 'critical',
            'occurrence_threshold'     => 2,
            'time_window_days'         => null,
            'escalation_action'        => 'expulsion_referral',
            'escalation_description'   => 'A second critical infraction in the academic year requires expulsion referral to the district authority.',
            'auto_notify_parent'       => true,
            'is_active'                => true,
            'legal_reference'          => 'Directive 150/2016, Art. 6(2)',
        ]);

        $this->command->info('✓ Seeded ' . InfractionDefinition::count() . ' infraction definitions');
        $this->command->info('✓ Seeded ' . EscalationRule::count() . ' escalation rules');
    }
}
