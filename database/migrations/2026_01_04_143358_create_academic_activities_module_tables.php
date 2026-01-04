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
        Schema::create('academic_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_assignment_id')->constrained()->onDelete('cascade');
            $table->foreignId('assessment_template_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('type', ['homework', 'assignment', 'exam']);
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('due_date');
            $table->dateTime('start_date')->nullable();
            $table->decimal('max_score', 10, 2)->default(100);
            $table->boolean('is_published')->default(false);
            $table->json('config')->nullable(); // For duration, randomization, late rules
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('division_id')->constrained()->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('activity_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_activity_id')->constrained()->onDelete('cascade');
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->dateTime('submitted_at')->nullable();
            $table->enum('status', ['pending', 'submitted', 'graded', 'late'])->default('pending');
            $table->decimal('score', 10, 2)->nullable();
            $table->text('feedback')->nullable();
            $table->foreignId('graded_by')->nullable()->constrained('users')->onDelete('set null');
            $table->dateTime('graded_at')->nullable();
            $table->integer('attempt_number')->default(1);
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
        });

        Schema::create('exam_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_activity_id')->constrained()->onDelete('cascade');
            $table->text('question_text');
            $table->enum('type', ['mcq', 'tf', 'short_answer', 'essay']);
            $table->json('options')->nullable();
            $table->text('correct_answer')->nullable();
            $table->decimal('points', 10, 2)->default(1);
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        Schema::create('activity_attachments', function (Blueprint $table) {
            $table->id();
            $table->morphs('attachable'); // activity or submission
            $table->string('file_path');
            $table->string('file_name');
            $table->string('file_type')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_attachments');
        Schema::dropIfExists('exam_questions');
        Schema::dropIfExists('activity_submissions');
        Schema::dropIfExists('academic_activities');
    }
};
