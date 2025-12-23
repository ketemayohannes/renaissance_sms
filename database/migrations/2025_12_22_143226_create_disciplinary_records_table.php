<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('disciplinary_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained()->onDelete('cascade');
            $table->date('incident_date');
            $table->string('incident_type'); // behavioral, academic, attendance, other
            $table->string('severity'); // minor, moderate, major, critical
            $table->text('description');
            $table->text('action_taken')->nullable();
            $table->foreignId('reported_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('handled_by')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('status', ['reported', 'under_review', 'resolved', 'escalated'])->default('reported');
            $table->date('resolution_date')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->boolean('notify_parent')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disciplinary_records');
    }
};
