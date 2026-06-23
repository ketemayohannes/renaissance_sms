<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('report_card_settings', function (Blueprint $table) {
            $table->json('yearly_config')->nullable()->after('template_config');
            $table->json('grade_scales')->nullable()->after('yearly_config');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('report_card_settings', function (Blueprint $table) {
            $table->dropColumn(['yearly_config', 'grade_scales']);
        });
    }
};
