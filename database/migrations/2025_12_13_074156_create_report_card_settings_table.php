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
        Schema::create('report_card_settings', function (Blueprint $table) {
            $table->id();
            $table->string('school_name')->default('Renaissance School');
            $table->text('school_address')->nullable();
            $table->string('website')->nullable();
            $table->string('telephone')->nullable();
            $table->string('logo_path')->nullable();
            $table->json('template_config')->nullable(); // Stores toggles: show_rank, show_conduct, etc.
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_card_settings');
    }
};
