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
            // Drop the old foreign key constraint
            $table->dropForeign(['grade_component_id']);
            
            // Rename the column
            $table->renameColumn('grade_component_id', 'assessment_template_id');
            
            // Add new foreign key constraint pointing to assessment_templates
            $table->foreign('assessment_template_id')
                  ->references('id')
                  ->on('assessment_templates')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_marks', function (Blueprint $table) {
            // Drop the new foreign key
            $table->dropForeign(['assessment_template_id']);
            
            // Rename back
            $table->renameColumn('assessment_template_id', 'grade_component_id');
            
            // Restore old foreign key
            $table->foreign('grade_component_id')
                  ->references('id')
                  ->on('grade_components')
                  ->onDelete('cascade');
        });
    }
};
