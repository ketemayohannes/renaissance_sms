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
        Schema::table('student_guardians', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('student_id')->constrained('users')->nullOnDelete();
            $table->boolean('is_emergency_contact')->default(false)->after('guardian_type');
            $table->json('communication_preferences')->nullable()->after('email');
            $table->text('address')->nullable()->after('relationship');
            
            // Create non-unique index first so MySQL doesn't complain about foreign key missing an index
            $table->index('student_id');
            $table->dropUnique('student_guardians_student_id_guardian_type_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_guardians', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['user_id', 'is_emergency_contact', 'communication_preferences', 'address']);
            
            // Re-add unique constraint (might fail if data violates it, but standard down method behavior)
            $table->unique(['student_id', 'guardian_type']);
        });
    }
};
