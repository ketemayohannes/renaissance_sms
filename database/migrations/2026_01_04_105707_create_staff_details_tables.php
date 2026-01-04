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
        Schema::create('academic_staff_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->string('teacher_rank')->nullable();
            $table->string('qualification_level')->nullable();
            $table->string('specialization')->nullable();
            $table->integer('periods_per_week')->nullable();
            $table->text('secondary_responsibilities')->nullable();
            $table->timestamps();
        });

        Schema::create('administrative_staff_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            // Add admin-specific fields here in the future
            $table->text('system_access_roles')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('administrative_staff_details');
        Schema::dropIfExists('academic_staff_details');
    }
};
