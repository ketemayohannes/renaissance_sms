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
        Schema::create('id_card_settings', function (Blueprint $table) {
            $table->id();
            $table->string('primary_color')->default('#1e3a8a');
            $table->string('secondary_color')->default('#3b82f6');
            $table->string('text_color')->default('#ffffff');
            $table->json('front_fields')->nullable();
            $table->json('back_fields')->nullable();
            $table->text('back_content')->nullable();
            $table->boolean('show_barcode')->default(false);
            $table->boolean('show_qr_code')->default(false);
            $table->string('photo_shape')->default('rounded'); // rounded, circle, square
            $table->timestamps();
        });

        // Insert default settings
        DB::table('id_card_settings')->insert([
            'primary_color' => '#1e3a8a',
            'secondary_color' => '#3b82f6',
            'text_color' => '#ffffff',
            'front_fields' => json_encode(['student_id', 'full_name', 'grade', 'gender', 'date_of_birth']),
            'back_fields' => json_encode(['guardian_name', 'guardian_phone', 'blood_group']),
            'back_content' => "1. This card is the property of Renaissance School.\n2. If found, please return to the school office.\n3. Student must carry this card at all times within school premises.",
            'show_barcode' => true,
            'show_qr_code' => false,
            'photo_shape' => 'rounded',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('id_card_settings');
    }
};
