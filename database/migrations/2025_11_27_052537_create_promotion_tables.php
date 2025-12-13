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
        // Promotion Rules
        Schema::create('promotion_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_grade_level_id')->constrained('grade_levels')->onDelete('cascade');
            $table->foreignId('to_grade_level_id')->constrained('grade_levels')->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained()->onDelete('cascade');
            $table->decimal('min_average', 5, 2)->default(50.00);
            $table->decimal('min_attendance_percent', 5, 2)->default(75.00);
            $table->integer('max_failed_subjects')->default(0);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Student Promotions (History)
        Schema::create('student_promotions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('from_academic_year_id')->constrained('academic_years')->onDelete('cascade');
            $table->foreignId('to_academic_year_id')->constrained('academic_years')->onDelete('cascade');
            $table->foreignId('from_grade_level_id')->constrained('grade_levels')->onDelete('cascade');
            $table->foreignId('to_grade_level_id')->constrained('grade_levels')->onDelete('cascade');
            $table->enum('status', ['promoted', 'retained', 'conditionally_promoted'])->default('promoted');
            $table->text('remarks')->nullable();
            $table->foreignId('processed_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_promotions');
        Schema::dropIfExists('promotion_rules');
    }
};
