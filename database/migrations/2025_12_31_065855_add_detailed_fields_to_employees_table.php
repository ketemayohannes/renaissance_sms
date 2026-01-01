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
            // Personal Information
            $table->string('grandfather_name')->nullable()->after('last_name');
            $table->enum('marital_status', ['single', 'married', 'divorced', 'widowed'])->nullable()->after('gender');
            
            // Address & ID Information
            $table->string('region')->nullable()->after('address');
            $table->string('zone')->nullable()->after('region');
            $table->string('woreda')->nullable()->after('zone');
            $table->string('national_id')->nullable()->after('woreda');
            $table->string('tin')->nullable()->after('national_id');
            $table->string('pension_number')->nullable()->after('tin');
            
            // Employment & Category
            $table->string('staff_category')->default('administrative')->after('department'); // academic, administrative, support, etc.
            
            // Payroll & Emergency Info
            $table->string('emergency_contact_name')->nullable()->after('basic_salary');
            $table->string('emergency_contact_phone')->nullable()->after('emergency_contact_name');
            $table->string('bank_name')->nullable()->after('emergency_contact_phone');
            $table->string('account_number')->nullable()->after('bank_name');
            
            // Professional/Teaching Specific
            $table->string('teacher_rank')->nullable(); // as per MoE standards
            $table->string('qualification_level')->nullable(); // Diploma, Degree, Masters, PhD
            $table->string('specialization')->nullable(); // Subject/Major
            $table->integer('periods_per_week')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'grandfather_name', 
                'marital_status', 
                'region', 
                'zone', 
                'woreda', 
                'national_id', 
                'tin', 
                'pension_number',
                'staff_category',
                'emergency_contact_name',
                'emergency_contact_phone',
                'bank_name',
                'account_number',
                'teacher_rank',
                'qualification_level',
                'specialization',
                'periods_per_week'
            ]);
        });
    }
};
