<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Truncate existing records to prevent FK and NULL constraint issues on existing data
        \Illuminate\Support\Facades\DB::table('disciplinary_records')->truncate();

        Schema::table('disciplinary_records', function (Blueprint $table) {
            // Drop legacy string columns only if they exist
            if (Schema::hasColumn('disciplinary_records', 'incident_type')) {
                $table->dropColumn('incident_type');
            }
            if (Schema::hasColumn('disciplinary_records', 'severity')) {
                $table->dropColumn('severity');
            }

            // Add new FK to infraction definitions (required)
            if (!Schema::hasColumn('disciplinary_records', 'infraction_definition_id')) {
                $table->uuid('infraction_definition_id')->after('academic_year_id');
                $table->foreign('infraction_definition_id')
                      ->references('id')
                      ->on('infraction_definitions')
                      ->onDelete('restrict');
            }

            // Add optional escalation tracking
            if (!Schema::hasColumn('disciplinary_records', 'escalation_rule_id')) {
                $table->uuid('escalation_rule_id')->nullable()->after('infraction_definition_id');
                $table->foreign('escalation_rule_id')
                      ->references('id')
                      ->on('escalation_rules')
                      ->onDelete('set null');
            }

            if (!Schema::hasColumn('disciplinary_records', 'escalation_action_applied')) {
                $table->string('escalation_action_applied')->nullable()->after('escalation_rule_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('disciplinary_records', function (Blueprint $table) {
            if (Schema::hasColumn('disciplinary_records', 'infraction_definition_id')) {
                $table->dropForeign(['infraction_definition_id']);
                $table->dropColumn('infraction_definition_id');
            }
            if (Schema::hasColumn('disciplinary_records', 'escalation_rule_id')) {
                $table->dropForeign(['escalation_rule_id']);
                $table->dropColumn('escalation_rule_id');
            }
            if (Schema::hasColumn('disciplinary_records', 'escalation_action_applied')) {
                $table->dropColumn('escalation_action_applied');
            }

            if (!Schema::hasColumn('disciplinary_records', 'incident_type')) {
                $table->string('incident_type')->after('academic_year_id');
            }
            if (!Schema::hasColumn('disciplinary_records', 'severity')) {
                $table->string('severity')->after('incident_type');
            }
        });
    }
};
