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
        Schema::table('student_enrollments', function (Blueprint $table) {
            if (!Schema::hasColumn('student_enrollments', 'end_date')) {
                $table->date('end_date')->nullable()->after('enrollment_date');
            }
            
            // Add standard indexes to support the foreign keys before dropping the unique constraint
            // We check if they exist first (though Laravel doesn't have hasIndex easily, we can just try adding them 
            // or assume they don't exist as separate indexes if the unique one was being used).
            // Actually, best to just add them. MySQL is smart enough to use them.
            try {
                $table->index('student_id');
                $table->index('academic_year_id');
            } catch (\Exception $e) {
                // Indexes might already exist
            }

            try {
                $table->dropUnique('student_year_unique');
            } catch (\Exception $e) {
                // Index might not exist or already dropped
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_enrollments', function (Blueprint $table) {
            $table->dropColumn('end_date');
            
            // Re-add the unique constraint
            $table->unique(['student_id', 'academic_year_id'], 'student_year_unique');
        });
    }
};
