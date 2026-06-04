<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('promotion_rules', function (Blueprint $table) {
            $table->unsignedBigInteger('to_grade_level_id')->nullable()->change();
            $table->json('major_subjects')->nullable();
            $table->json('conditional_rules')->nullable();
            $table->string('failed_action')->default('retained');
        });

        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE student_promotions MODIFY COLUMN status ENUM('promoted', 'retained', 'conditionally_promoted', 're_exam', 'graduated') DEFAULT 'promoted'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('promotion_rules', function (Blueprint $table) {
            $table->unsignedBigInteger('to_grade_level_id')->nullable(false)->change();
            $table->dropColumn(['major_subjects', 'conditional_rules', 'failed_action']);
        });

        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE student_promotions MODIFY COLUMN status ENUM('promoted', 'retained', 'conditionally_promoted') DEFAULT 'promoted'");
        }
    }
};
