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
        Schema::table('id_card_settings', function (Blueprint $table) {
            $table->string('school_name')->nullable()->after('id');
            $table->string('logo_path')->nullable()->after('school_name');
        });

        // Initialize with default if exists
        $reportSettings = DB::table('report_card_settings')->first();
        if ($reportSettings) {
            DB::table('id_card_settings')->update([
                'school_name' => $reportSettings->school_name,
                'logo_path' => $reportSettings->logo_path,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('id_card_settings', function (Blueprint $table) {
            $table->dropColumn(['school_name', 'logo_path']);
        });
    }
};
