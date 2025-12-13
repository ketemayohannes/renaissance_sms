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
        // Transcript Configurations (Admin-defined grade ranges per division)
        Schema::create('transcript_configurations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('division_id')->constrained()->onDelete('cascade');
            $table->foreignId('start_grade_level_id')->constrained('grade_levels')->onDelete('cascade');
            $table->foreignId('end_grade_level_id')->constrained('grade_levels')->onDelete('cascade');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Transcripts (Generated academic records)
        Schema::create('transcripts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['withdrawal', 'division_completion', 'graduation'])->default('division_completion');
            $table->json('academic_years_covered'); // Array of academic year IDs
            $table->json('grade_levels_covered'); // Array of grade level IDs
            $table->string('file_path'); // PDF storage path
            $table->timestamp('generated_at');
            $table->foreignId('generated_by')->constrained('users')->onDelete('cascade');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        // Transcript Details (Individual subject records per year/grade)
        Schema::create('transcript_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transcript_id')->constrained()->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained()->onDelete('cascade');
            $table->foreignId('grade_level_id')->constrained()->onDelete('cascade');
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->decimal('yearly_average', 5, 2);
            $table->string('grade', 2)->nullable(); // A+, A, B+, etc.
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transcript_details');
        Schema::dropIfExists('transcripts');
        Schema::dropIfExists('transcript_configurations');
    }
};
