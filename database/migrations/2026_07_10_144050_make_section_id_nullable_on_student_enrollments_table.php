<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Enrolling a promoted student into a year that has no sections created yet
     * (e.g. right after rolling the active academic year forward) must still record
     * that the student belongs to the new year, with the section left unassigned
     * until a registrar creates sections and assigns one.
     */
    public function up(): void
    {
        Schema::table('student_enrollments', function (Blueprint $table) {
            $table->foreignId('section_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_enrollments', function (Blueprint $table) {
            $table->foreignId('section_id')->nullable(false)->change();
        });
    }
};
