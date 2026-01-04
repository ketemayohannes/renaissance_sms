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
        Schema::table('academic_staff_details', function (Blueprint $table) {
            $table->string('institution')->nullable();
            $table->year('graduation_year')->nullable();
            $table->string('last_degree')->nullable();
        });

        Schema::table('administrative_staff_details', function (Blueprint $table) {
            $table->string('qualification_level')->nullable();
            $table->string('specialization')->nullable();
            $table->string('institution')->nullable();
            $table->year('graduation_year')->nullable();
            $table->string('last_degree')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('academic_staff_details', function (Blueprint $table) {
            $table->dropColumn(['institution', 'graduation_year', 'last_degree']);
        });

        Schema::table('administrative_staff_details', function (Blueprint $table) {
            $table->dropColumn(['qualification_level', 'specialization', 'institution', 'graduation_year', 'last_degree']);
        });
    }
};
