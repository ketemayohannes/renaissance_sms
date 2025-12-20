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
        Schema::create('academic_report_settings', function (Blueprint $table) {
            $table->id();
            $table->string('roster_logo_path')->nullable();
            $table->string('school_name')->nullable();
            $table->json('display_options')->nullable(); // To store portrait/landscape etc.
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_report_settings');
    }
};
