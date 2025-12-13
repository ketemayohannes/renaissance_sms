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
        Schema::table('student_marks', function (Blueprint $table) {
            // Add term_id column if it doesn't exist
            if (!Schema::hasColumn('student_marks', 'term_id')) {
                $table->foreignId('term_id')->after('grade_component_id')->constrained()->onDelete('cascade');
            }
            
            // Add subject_id column if it doesn't exist
            if (!Schema::hasColumn('student_marks', 'subject_id')) {
                $table->foreignId('subject_id')->after('term_id')->constrained()->onDelete('cascade');
            }
        });
        
        // Rename marks_obtained to score if it exists
        if (Schema::hasColumn('student_marks', 'marks_obtained') && !Schema::hasColumn('student_marks', 'score')) {
            Schema::table('student_marks', function (Blueprint $table) {
                $table->renameColumn('marks_obtained', 'score');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rename score back to marks_obtained
        if (Schema::hasColumn('student_marks', 'score') && !Schema::hasColumn('student_marks', 'marks_obtained')) {
            Schema::table('student_marks', function (Blueprint $table) {
                $table->renameColumn('score', 'marks_obtained');
            });
        }
        
        Schema::table('student_marks', function (Blueprint $table) {
            if (Schema::hasColumn('student_marks', 'subject_id')) {
                $table->dropForeign(['subject_id']);
                $table->dropColumn('subject_id');
            }
            
            if (Schema::hasColumn('student_marks', 'term_id')) {
                $table->dropForeign(['term_id']);
                $table->dropColumn('term_id');
            }
        });
    }
};
