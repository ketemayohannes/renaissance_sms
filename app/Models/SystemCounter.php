<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\DB;

class SystemCounter extends Model
{
    protected $fillable = ['key', 'value'];

    /**
     * Atomically increment and get the next value for a key.
     */
    public static function next($key, $startValue = 0)
    {
        return DB::transaction(function () use ($key, $startValue) {
            $counter = self::where('key', $key)->lockForUpdate()->first();

            if (!$counter) {
                $counter = self::create(['key' => $key, 'value' => $startValue]);
            }

            $counter->increment('value');
            return $counter->value;
        });
    }
}
