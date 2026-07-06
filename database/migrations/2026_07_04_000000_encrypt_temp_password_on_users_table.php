<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;

return new class extends Migration
{
    /**
     * Encrypt the plaintext initial-login credential at rest so it can no
     * longer be read from a raw database dump. The application decrypts it
     * transparently via the model's `encrypted` cast (needs APP_KEY).
     */
    public function up(): void
    {
        // Encrypted payloads (~250 chars) can exceed the old varchar(255).
        Schema::table('users', function (Blueprint $table) {
            $table->text('temp_password')->nullable()->change();
        });

        // Encrypt existing plaintext values in place. Idempotent: values that
        // are already encrypted decrypt cleanly and are skipped.
        DB::table('users')
            ->whereNotNull('temp_password')
            ->where('temp_password', '!=', '')
            ->orderBy('id')
            ->chunkById(200, function ($users) {
                foreach ($users as $user) {
                    try {
                        Crypt::decryptString($user->temp_password);
                        continue; // already encrypted
                    } catch (\Throwable $e) {
                        // plaintext — encrypt below
                    }

                    DB::table('users')
                        ->where('id', $user->id)
                        ->update(['temp_password' => Crypt::encryptString($user->temp_password)]);
                }
            });
    }

    /**
     * Reverse: decrypt back to plaintext and restore the varchar column.
     */
    public function down(): void
    {
        DB::table('users')
            ->whereNotNull('temp_password')
            ->where('temp_password', '!=', '')
            ->orderBy('id')
            ->chunkById(200, function ($users) {
                foreach ($users as $user) {
                    try {
                        $plain = Crypt::decryptString($user->temp_password);
                    } catch (\Throwable $e) {
                        continue; // already plaintext
                    }

                    DB::table('users')
                        ->where('id', $user->id)
                        ->update(['temp_password' => $plain]);
                }
            });

        Schema::table('users', function (Blueprint $table) {
            $table->string('temp_password')->nullable()->change();
        });
    }
};
