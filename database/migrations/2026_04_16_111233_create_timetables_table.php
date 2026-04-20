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
        Schema::create('timetables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('section_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_period_id')->constrained()->cascadeOnDelete();
            $table->integer('day_of_week')->comment('1=Monday, 7=Sunday');
            $table->foreignId('teacher_assignment_id')->constrained()->cascadeOnDelete();
            $table->string('room_number')->nullable();
            
            // A section can only have one assigned activity during a specific period on a specific day
            $table->unique(['academic_year_id', 'section_id', 'day_of_week', 'class_period_id'], 'uq_timetable_slot');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timetables');
    }
};
