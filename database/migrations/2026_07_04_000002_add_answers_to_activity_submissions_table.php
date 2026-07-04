<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Persist the answers a student submits for an online exam, keyed by
     * question id, so teachers can review responses (especially subjective
     * ones) instead of grading blind. Previously these were written to a
     * non-existent "config" attribute and silently discarded.
     */
    public function up(): void
    {
        Schema::table('activity_submissions', function (Blueprint $table) {
            $table->json('answers')->nullable()->after('submitted_at');
        });
    }

    public function down(): void
    {
        Schema::table('activity_submissions', function (Blueprint $table) {
            $table->dropColumn('answers');
        });
    }
};
