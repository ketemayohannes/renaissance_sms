<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;

class NumberFormatter
{
    /**
     * Format a number, removing unnecessary decimal places.
     * Shows decimals only when the number is not a whole number.
     * 
     * @param float|int|null $number The number to format
     * @param int $maxDecimals Maximum decimal places to show (default 2)
     * @return string
     */
    public static function format($number, int $maxDecimals = 2): string
    {
        if ($number === null) {
            return '-';
        }
        
        if (!is_numeric($number)) {
            return (string) $number;
        }
        
        $number = (float) $number;
        
        // Format with absolute decimal points and no grouping to avoid locale issues
        // We use a high precision temporarily to avoid premature rounding by number_format itself
        $formatted = number_format($number, $maxDecimals, '.', '');
        
        // Remove trailing zeros and the decimal point if it becomes unnecessary
        if (strpos($formatted, '.') !== false) {
            $formatted = rtrim($formatted, '0');
            $formatted = rtrim($formatted, '.');
        }
        
        return $formatted;
    }
}
