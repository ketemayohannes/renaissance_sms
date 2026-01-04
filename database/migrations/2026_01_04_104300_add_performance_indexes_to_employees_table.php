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
            $table->index('status');
            $table->index('designation');
            $table->index('staff_category');
            $table->index(['first_name', 'middle_name', 'last_name'], 'emp_name_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['designation']);
            $table->dropIndex(['staff_category']);
            $table->dropIndex('emp_name_index');
        });
    }
};
