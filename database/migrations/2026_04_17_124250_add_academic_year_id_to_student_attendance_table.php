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
        Schema::table('student_attendance', function (Blueprint $table) {
            $table->foreignId('academic_year_id')->nullable()->after('section_id')->constrained()->onDelete('cascade');
            $table->index(['academic_year_id', 'attendance_date'], 'sa_academic_date_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_attendance', function (Blueprint $table) {
            $table->dropForeign(['academic_year_id']);
            $table->dropIndex('sa_academic_date_idx');
            $table->dropColumn('academic_year_id');
        });
    }
};
