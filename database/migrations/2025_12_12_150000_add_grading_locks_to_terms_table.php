<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('terms', function (Blueprint $table) {
            $table->boolean('is_grading_open')->default(true)->after('end_date')->comment('Controls Subject Wise Grading');
            $table->boolean('is_master_grading_open')->default(true)->after('is_grading_open')->comment('Controls Master Sheet Grading');
        });
    }

    public function down()
    {
        Schema::table('terms', function (Blueprint $table) {
            $table->dropColumn(['is_grading_open', 'is_master_grading_open']);
        });
    }
};
