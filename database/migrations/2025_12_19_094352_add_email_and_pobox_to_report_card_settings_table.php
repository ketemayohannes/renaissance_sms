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
        Schema::table('report_card_settings', function (Blueprint $table) {
            $table->string('email')->nullable()->after('telephone');
            $table->string('po_box')->nullable()->after('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('report_card_settings', function (Blueprint $table) {
            $table->dropColumn(['email', 'po_box']);
        });
    }
};
