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
        Schema::create('student_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->enum('old_status', ['active', 'inactive', 'withdrawn', 'graduated', 'transferred', 'dropped_out'])->nullable();
            $table->enum('new_status', ['active', 'inactive', 'withdrawn', 'graduated', 'transferred', 'dropped_out']);
            $table->string('reason')->nullable();
            $table->text('notes')->nullable();
            $table->date('effective_date')->nullable();
            $table->foreignId('changed_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_status_history');
    }
};
