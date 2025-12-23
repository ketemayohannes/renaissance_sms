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
        Schema::table('students', function (Blueprint $table) {
            // Index for name-based searches
            $table->index(['first_name', 'father_name', 'grandfather_name'], 'students_name_compound_index');
            
            // Indexes for filtering
            $table->index('gender');
            $table->index('is_active');
            
            // Index for admission number (already unique, but good to be explicit for searching if unique didn't add it consistently in all DBs, though unique usually does. Let's skip redudant one).
            // Admission number is unique so it has an index.
            
            // Consider checking if user_id index exists (foreignId constrained usually adds it).
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropIndex('students_name_compound_index');
            $table->dropIndex(['gender']);
            $table->dropIndex(['is_active']);
        });
    }
};
