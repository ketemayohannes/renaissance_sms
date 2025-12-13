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
        // Divisions (KG, Elementary, High School)
        Schema::create('divisions', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // KG, Elementary, High School
            $table->string('code', 10)->unique(); // KG, ES, HS
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Academic Years
        Schema::create('academic_years', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // 2024/2025
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        // Terms (Quarters/Semesters)
        Schema::create('terms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained()->onDelete('cascade');
            $table->string('name'); // Quarter 1, Semester 1
            $table->enum('type', ['quarter', 'semester']);
            $table->integer('term_number'); // 1, 2, 3, 4
            $table->date('start_date');
            $table->date('end_date');
            $table->timestamps();
        });

        // Grade Levels
        Schema::create('grade_levels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('division_id')->constrained()->onDelete('cascade');
            $table->string('name'); // Grade 11, KG 1
            $table->string('code', 10)->unique(); // G11, KG1
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Sections
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grade_level_id')->constrained()->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained()->onDelete('cascade');
            $table->string('name'); // Section A, Section B
            $table->integer('capacity')->default(30);
            $table->foreignId('homeroom_teacher_id')->nullable()->constrained('users')->onDelete('set null');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Subjects
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Mathematics, English
            $table->string('code', 20)->unique(); // MATH, ENG
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Grade Level Subjects (which subjects are taught in which grade)
        Schema::create('grade_level_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grade_level_id')->constrained()->onDelete('cascade');
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained()->onDelete('cascade');
            $table->boolean('is_required')->default(true);
            $table->timestamps();
            $table->unique(['grade_level_id', 'subject_id', 'academic_year_id'], 'gls_unique');
        });

        // Grade Components (Dynamic Assessment Structure)
        Schema::create('grade_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grade_level_subject_id')->constrained()->onDelete('cascade');
            $table->foreignId('term_id')->constrained()->onDelete('cascade');
            $table->string('name'); // Test 1, Midterm, Project
            $table->decimal('weight', 5, 2); // 30.00 (percentage)
            $table->decimal('max_score', 5, 2)->default(100.00);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Students
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('student_id')->unique(); // School ID
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->enum('gender', ['M', 'F']);
            $table->date('date_of_birth');
            $table->string('admission_number')->unique();
            $table->date('admission_date');
            $table->string('photo')->nullable();
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Student Enrollments (which section a student is in)
        Schema::create('student_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('section_id')->constrained()->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained()->onDelete('cascade');
            $table->date('enrollment_date');
            $table->enum('status', ['active', 'transferred', 'graduated', 'withdrawn'])->default('active');
            $table->timestamps();
            $table->unique(['student_id', 'academic_year_id'], 'student_year_unique');
        });

        // Teacher Assignments (which teacher teaches which subject in which section)
        Schema::create('teacher_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('section_id')->constrained()->onDelete('cascade');
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            $table->unique(['teacher_id', 'section_id', 'subject_id', 'academic_year_id'], 'teacher_assignment_unique');
        });

        // Conduct Grades
        Schema::create('conduct_grades', function (Blueprint $table) {
            $table->id();
            $table->string('grade', 1); // A, B, C
            $table->string('description'); // Excellent, Needs Improvement, Poor
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conduct_grades');
        Schema::dropIfExists('teacher_assignments');
        Schema::dropIfExists('student_enrollments');
        Schema::dropIfExists('students');
        Schema::dropIfExists('grade_components');
        Schema::dropIfExists('grade_level_subjects');
        Schema::dropIfExists('subjects');
        Schema::dropIfExists('sections');
        Schema::dropIfExists('grade_levels');
        Schema::dropIfExists('terms');
        Schema::dropIfExists('academic_years');
        Schema::dropIfExists('divisions');
    }
};
