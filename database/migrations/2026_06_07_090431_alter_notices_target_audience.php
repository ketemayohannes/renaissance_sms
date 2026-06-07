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
        Schema::table('notices', function (Blueprint $table) {
            $table->string('target_audience', 50)->change();
        });

        // Migrate existing lowercase/plural data to match the app's conventions
        \Illuminate\Support\Facades\DB::table('notices')
            ->where('target_audience', 'all')
            ->update(['target_audience' => 'All']);

        \Illuminate\Support\Facades\DB::table('notices')
            ->where('target_audience', 'parents')
            ->update(['target_audience' => 'Parent']);

        \Illuminate\Support\Facades\DB::table('notices')
            ->where('target_audience', 'teachers')
            ->update(['target_audience' => 'Teacher']);

        \Illuminate\Support\Facades\DB::table('notices')
            ->where('target_audience', 'students')
            ->update(['target_audience' => 'Student']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert data to match original enum values
        \Illuminate\Support\Facades\DB::table('notices')
            ->where('target_audience', 'All')
            ->update(['target_audience' => 'all']);

        \Illuminate\Support\Facades\DB::table('notices')
            ->where('target_audience', 'Parent')
            ->update(['target_audience' => 'parents']);

        \Illuminate\Support\Facades\DB::table('notices')
            ->where('target_audience', 'Teacher')
            ->update(['target_audience' => 'teachers']);

        \Illuminate\Support\Facades\DB::table('notices')
            ->where('target_audience', 'Student')
            ->update(['target_audience' => 'students']);

        Schema::table('notices', function (Blueprint $table) {
            $table->enum('target_audience', ['all', 'students', 'teachers', 'parents', 'staff'])->change();
        });
    }
};
