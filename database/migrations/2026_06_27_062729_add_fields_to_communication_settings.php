<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('communication_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('communication_settings', 'in_app_enabled')) {
                $table->boolean('in_app_enabled')->default(true)->after('email_enabled');
            }
            if (!Schema::hasColumn('communication_settings', 'resend_api_key')) {
                $table->string('resend_api_key')->nullable()->after('smsethiopia_api_key');
            }
        });
    }

    public function down(): void
    {
        Schema::table('communication_settings', function (Blueprint $table) {
            $table->dropColumn(['in_app_enabled', 'resend_api_key']);
        });
    }
};
