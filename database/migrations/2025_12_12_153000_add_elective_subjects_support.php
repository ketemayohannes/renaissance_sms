<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->boolean('is_elective')->default(false)->after('description');
        });

        Schema::create('student_electives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['student_id', 'subject_id', 'academic_year_id'], 'student_elective_unique');
        });
    }

    public function down()
    {
        Schema::dropIfExists('student_electives');
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropColumn('is_elective');
        });
    }
};
