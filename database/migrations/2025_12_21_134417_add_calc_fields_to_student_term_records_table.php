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
        Schema::table('student_term_records', function (Blueprint $table) {
            $table->decimal('total_score', 10, 2)->nullable()->after('behavior_traits');
            $table->decimal('average_score', 10, 2)->nullable()->after('total_score');
            $table->integer('rank')->nullable()->after('average_score');
            $table->integer('rank_out_of')->nullable()->after('rank');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_term_records', function (Blueprint $table) {
            $table->dropColumn(['total_score', 'average_score', 'rank', 'rank_out_of']);
        });
    }
};
