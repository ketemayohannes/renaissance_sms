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
        Schema::create('student_term_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('term_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();

            $table->integer('total_attendance_days')->nullable();
            $table->integer('days_absent')->default(0);
            $table->string('conduct_grade')->nullable(); // A, B, C...
            
            $table->text('homeroom_teacher_comment')->nullable();
            $table->text('principal_comment')->nullable();
            
            $table->json('behavior_traits')->nullable(); // JSON object for checked boxes
            
            $table->timestamps();

            $table->unique(['student_id', 'term_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_term_records');
    }
};
