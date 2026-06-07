<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('communication_settings', function (Blueprint $table) {
            $table->string('sms_provider')->default('africastalking')->after('sms_enabled');
            $table->string('smsethiopia_api_key')->nullable()->after('africastalking_sandbox');
        });
    }

    public function down(): void
    {
        Schema::table('communication_settings', function (Blueprint $table) {
            $table->dropColumn(['sms_provider', 'smsethiopia_api_key']);
        });
    }
};
