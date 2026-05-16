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
            $table->text('section_remark')->nullable()->after('comparison_comment');
            
            // We can keep the old ones for now to avoid errors if they are referenced, 
            // but we'll stop using them in the UI.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subject_analysis_reports', function (Blueprint $table) {
            $table->dropColumn('section_remark');
        });
    }
};
