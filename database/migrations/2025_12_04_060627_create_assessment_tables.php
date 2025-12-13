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
        // Assessment Types Table
        if (!Schema::hasTable('assessment_types')) {
            Schema::create('assessment_types', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100); // Quiz, Test, Assignment, Project, etc.
                $table->string('code', 20)->unique(); // QUIZ, TEST, ASSIGN, PROJ
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // Modify existing grade_components table
        if (Schema::hasTable('grade_components')) {
            Schema::table('grade_components', function (Blueprint $table) {
                // Add new columns if they don't exist
                if (!Schema::hasColumn('grade_components', 'assessment_type_id')) {
                    $table->foreignId('assessment_type_id')->nullable()->after('term_id')->constrained()->onDelete('cascade');
                }
                if (!Schema::hasColumn('grade_components', 'academic_year_id')) {
                    $table->foreignId('academic_year_id')->nullable()->after('id')->constrained()->onDelete('cascade');
                }
                if (!Schema::hasColumn('grade_components', 'grade_level_id')) {
                    $table->foreignId('grade_level_id')->nullable()->after('academic_year_id')->constrained()->onDelete('cascade');
                }
                if (!Schema::hasColumn('grade_components', 'subject_id')) {
                    $table->foreignId('subject_id')->nullable()->after('grade_level_id')->constrained()->onDelete('cascade');
                }
                if (!Schema::hasColumn('grade_components', 'order')) {
                    $table->integer('order')->default(0)->after('sort_order');
                }
                if (!Schema::hasColumn('grade_components', 'is_active')) {
                    $table->boolean('is_active')->default(true)->after('order');
                }
                
                // Note: We'll keep grade_level_subject_id for backward compatibility
                // In the future, we can migrate data and remove it
            });
        }

        // Add grade_component_id to student_marks table if it doesn't exist
        if (!Schema::hasColumn('student_marks', 'grade_component_id')) {
            Schema::table('student_marks', function (Blueprint $table) {
                $table->foreignId('grade_component_id')->nullable()->after('subject_id')->constrained()->onDelete('cascade');
                $table->text('remarks')->nullable()->after('score');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove grade_component_id from student_marks if it exists
        if (Schema::hasColumn('student_marks', 'grade_component_id')) {
            Schema::table('student_marks', function (Blueprint $table) {
                $table->dropForeign(['grade_component_id']);
                $table->dropColumn(['grade_component_id', 'remarks']);
            });
        }

        // Remove new columns from grade_components
        Schema::table('grade_components', function (Blueprint $table) {
            $table->dropForeign(['assessment_type_id']);
            $table->dropForeign(['academic_year_id']);
            $table->dropForeign(['grade_level_id']);
            $table->dropForeign(['subject_id']);
            $table->dropColumn(['assessment_type_id', 'academic_year_id', 'grade_level_id', 'subject_id', 'order', 'is_active']);
        });

        Schema::dropIfExists('assessment_types');
    }
};
