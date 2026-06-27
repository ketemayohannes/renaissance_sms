<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // Seed default school identity values
        DB::table('app_settings')->insert([
            ['key' => 'school.name',     'value' => 'Renaissance School',    'created_at' => now(), 'updated_at' => now()],
            ['key' => 'school.timezone', 'value' => 'Africa/Addis_Ababa',    'created_at' => now(), 'updated_at' => now()],
            ['key' => 'school.logo_path','value' => null,                    'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('app_settings');
    }
};
