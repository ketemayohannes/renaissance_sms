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
        Schema::table('student_promotions', function (Blueprint $table) {
            $table->foreignId('to_section_id')->nullable()->after('to_grade_level_id')->constrained('sections')->onDelete('set null');
            $table->boolean('is_enrolled')->default(false)->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_promotions', function (Blueprint $table) {
            $table->dropForeign(['to_section_id']);
            $table->dropColumn(['to_section_id', 'is_enrolled']);
        });
    }
};
