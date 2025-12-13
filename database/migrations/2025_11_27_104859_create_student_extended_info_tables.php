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
        // Add additional fields to students table
        Schema::table('students', function (Blueprint $table) {
            $table->string('father_name')->after('first_name');
            $table->string('grandfather_name')->after('father_name');
            $table->string('place_of_birth')->nullable()->after('date_of_birth');
            $table->string('nationality')->default('Ethiopian')->after('place_of_birth');
            $table->string('language_spoken')->nullable()->after('nationality');
            $table->string('subcity')->nullable()->after('address');
            $table->string('woreda')->nullable()->after('subcity');
            $table->string('house_number')->nullable()->after('woreda');
        });

        // Student Guardians Table
        Schema::create('student_guardians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->enum('guardian_type', ['primary', 'secondary']);
            $table->string('photo')->nullable();
            $table->string('first_name');
            $table->string('father_name');
            $table->string('grandfather_name');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->string('relationship')->nullable(); // Father, Mother, Uncle, etc.
            $table->timestamps();
            
            // A student can have only one primary and one secondary guardian
            $table->unique(['student_id', 'guardian_type']);
        });

        // Student Medical Information Table
        Schema::create('student_medical_info', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->unique()->constrained()->onDelete('cascade');
            $table->enum('blood_group', ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'])->nullable();
            $table->text('medical_issues')->nullable();
            $table->text('current_medication')->nullable();
            $table->text('allergies')->nullable();
            $table->string('emergency_contact')->nullable();
            $table->timestamps();
        });

        // Student Transportation Table
        Schema::create('student_transportation', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->unique()->constrained()->onDelete('cascade');
            $table->string('driver_id')->nullable();
            $table->string('driver_photo')->nullable();
            $table->string('driver_first_name')->nullable();
            $table->string('driver_father_name')->nullable();
            $table->string('driver_grandfather_name')->nullable();
            $table->string('license_number')->nullable();
            $table->string('vehicle_plate')->nullable();
            $table->string('route')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_transportation');
        Schema::dropIfExists('student_medical_info');
        Schema::dropIfExists('student_guardians');
        
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn([
                'father_name',
                'grandfather_name',
                'place_of_birth',
                'nationality',
                'language_spoken',
                'subcity',
                'woreda',
                'house_number'
            ]);
        });
    }
};
