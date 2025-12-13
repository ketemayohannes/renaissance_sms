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
        // Student Marks (individual component scores)
        Schema::create('student_marks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('grade_component_id')->constrained()->onDelete('cascade');
            $table->foreignId('section_id')->constrained()->onDelete('cascade');
            $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade');
            $table->decimal('marks_obtained', 5, 2);
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->unique(['student_id', 'grade_component_id'], 'student_component_unique');
        });

        // Term Results (aggregated per subject per term)
        Schema::create('term_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->foreignId('term_id')->constrained()->onDelete('cascade');
            $table->foreignId('section_id')->constrained()->onDelete('cascade');
            $table->decimal('total_marks', 5, 2);
            $table->decimal('percentage', 5, 2);
            $table->string('grade', 2)->nullable(); // A+, A, B+, etc.
            $table->timestamps();
            $table->unique(['student_id', 'subject_id', 'term_id'], 'term_result_unique');
        });

        // Semester Results (aggregated per subject per semester)
        Schema::create('semester_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained()->onDelete('cascade');
            $table->integer('semester_number'); // 1 or 2
            $table->decimal('total_marks', 5, 2);
            $table->decimal('percentage', 5, 2);
            $table->string('grade', 2)->nullable();
            $table->timestamps();
            $table->unique(['student_id', 'subject_id', 'academic_year_id', 'semester_number'], 'semester_result_unique');
        });

        // Yearly Results (aggregated per subject for the year)
        Schema::create('yearly_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained()->onDelete('cascade');
            $table->decimal('total_marks', 5, 2);
            $table->decimal('percentage', 5, 2);
            $table->string('grade', 2)->nullable();
            $table->timestamps();
            $table->unique(['student_id', 'subject_id', 'academic_year_id'], 'yearly_result_unique');
        });

        // Student Overall Performance (per term/semester/year)
        Schema::create('student_overall_performance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained()->onDelete('cascade');
            $table->enum('period_type', ['quarter', 'semester', 'year']);
            $table->integer('period_number')->nullable(); // 1, 2, 3, 4 for quarters; 1, 2 for semesters
            $table->decimal('total_marks', 8, 2);
            $table->decimal('average_percentage', 5, 2);
            $table->integer('total_subjects');
            $table->foreignId('conduct_grade_id')->nullable()->constrained()->onDelete('set null');
            $table->text('teacher_remarks')->nullable();
            $table->text('principal_remarks')->nullable();
            $table->timestamps();
        });

        // Rankings (section, grade, division)
        Schema::create('rankings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained()->onDelete('cascade');
            $table->enum('period_type', ['quarter', 'semester', 'year']);
            $table->integer('period_number')->nullable();
            $table->enum('rank_type', ['section', 'grade', 'division']);
            $table->foreignId('section_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('grade_level_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('division_id')->nullable()->constrained()->onDelete('cascade');
            $table->integer('rank');
            $table->integer('total_students');
            $table->decimal('average_percentage', 5, 2);
            $table->timestamps();
        });

        // Report Cards (generated PDFs)
        Schema::create('report_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained()->onDelete('cascade');
            $table->enum('report_type', ['quarter', 'semester', 'year']);
            $table->integer('report_number')->nullable();
            $table->string('file_path');
            $table->timestamp('generated_at');
            $table->foreignId('generated_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_cards');
        Schema::dropIfExists('rankings');
        Schema::dropIfExists('student_overall_performance');
        Schema::dropIfExists('yearly_results');
        Schema::dropIfExists('semester_results');
        Schema::dropIfExists('term_results');
        Schema::dropIfExists('student_marks');
    }
};
