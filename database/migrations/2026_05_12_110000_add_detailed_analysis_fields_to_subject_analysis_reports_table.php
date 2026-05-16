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
        Schema::table('subject_analysis_reports', function (Blueprint $table) {
            $table->text('problems_encountered')->nullable();
            $table->text('solutions_implemented')->nullable();
            $table->text('additional_comment')->nullable();
            
            // Note: comparison_comment will be used for field A
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subject_analysis_reports', function (Blueprint $table) {
            $table->dropColumn(['problems_encountered', 'solutions_implemented', 'additional_comment']);
        });
    }
};
