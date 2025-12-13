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
        Schema::table('students', function (Blueprint $table) {
            // Remove old place_of_birth field and add new structured fields
            $table->dropColumn('place_of_birth');
            $table->string('birth_country')->nullable()->after('date_of_birth');
            $table->string('birth_city')->nullable()->after('birth_country');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['birth_country', 'birth_city']);
            $table->string('place_of_birth')->nullable()->after('date_of_birth');
        });
    }
};
