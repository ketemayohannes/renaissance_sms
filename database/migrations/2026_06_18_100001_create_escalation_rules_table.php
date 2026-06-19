<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('escalation_rules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('infraction_definition_id')->nullable();
            $table->foreign('infraction_definition_id')
                  ->references('id')
                  ->on('infraction_definitions')
                  ->onDelete('cascade');
            $table->enum('tier', ['minor', 'moderate', 'critical']);
            $table->unsignedInteger('occurrence_threshold');
            $table->unsignedInteger('time_window_days')->nullable();
            $table->string('escalation_action');
            $table->text('escalation_description')->nullable();
            $table->boolean('auto_notify_parent')->default(true);
            $table->boolean('is_active')->default(true);
            $table->string('legal_reference')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('escalation_rules');
    }
};
