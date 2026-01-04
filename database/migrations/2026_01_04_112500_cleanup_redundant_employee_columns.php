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
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'teacher_rank',
                'qualification_level',
                'specialization',
                'periods_per_week'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('teacher_rank')->nullable();
            $table->string('qualification_level')->nullable();
            $table->string('specialization')->nullable();
            $table->integer('periods_per_week')->nullable();
        });
    }
};
