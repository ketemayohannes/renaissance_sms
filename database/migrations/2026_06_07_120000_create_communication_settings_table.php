<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('communication_settings', function (Blueprint $table) {
            $table->id();
            // Global Toggles
            $table->boolean('sms_enabled')->default(false);
            $table->boolean('email_enabled')->default(false);
            
            // Africa's Talking SMS API Credentials
            $table->string('africastalking_username')->nullable();
            $table->string('africastalking_api_key')->nullable();
            $table->string('africastalking_from')->nullable(); // Sender ID or Shortcode
            $table->boolean('africastalking_sandbox')->default(true);
            
            // SMTP Email Credentials
            $table->string('mail_mailer')->default('smtp');
            $table->string('mail_host')->nullable();
            $table->integer('mail_port')->nullable();
            $table->string('mail_username')->nullable();
            $table->string('mail_password')->nullable();
            $table->string('mail_encryption')->nullable();
            $table->string('mail_from_address')->nullable();
            $table->string('mail_from_name')->nullable();

            // Event-specific mappings (JSON of event toggles for SMS/Email)
            $table->json('event_settings')->nullable();
            $table->timestamps();
        });

        // Insert default settings
        DB::table('communication_settings')->insert([
            'sms_enabled' => false,
            'email_enabled' => false,
            'africastalking_username' => 'sandbox',
            'africastalking_sandbox' => true,
            'event_settings' => json_encode([
                'notice' => ['sms' => false, 'email' => true],
                'absence' => ['sms' => true, 'email' => true],
                'message' => ['sms' => false, 'email' => true],
                'export' => ['sms' => false, 'email' => true],
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('communication_settings');
    }
};
