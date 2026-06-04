<?php
/**
 * Migration to update student_enrollments status enum.
 * Created to fix 500 error during student withdrawal and promotion.
 */

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
        // Using raw SQL for ENUM update as it's more reliable across different Laravel/Doctrine versions
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE student_enrollments MODIFY COLUMN status ENUM('active', 'transferred', 'graduated', 'withdrawn', 'dropped_out', 'completed') DEFAULT 'active'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE student_enrollments MODIFY COLUMN status ENUM('active', 'transferred', 'graduated', 'withdrawn') DEFAULT 'active'");
        }
    }
};
