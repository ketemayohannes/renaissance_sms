<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Whether a subject is an elective can differ by grade and academic year (e.g. CTE
     * subjects were electives in 2025/26 and are regular subjects from 2026/27). NULL means
     * "use the subject's own setting", so every existing year is unchanged.
     */
    public function up(): void
    {
        Schema::table('grade_level_subjects', function (Blueprint $table) {
            $table->boolean('is_elective')->nullable()->after('is_required');
        });
    }

    public function down(): void
    {
        Schema::table('grade_level_subjects', function (Blueprint $table) {
            $table->dropColumn('is_elective');
        });
    }
};
