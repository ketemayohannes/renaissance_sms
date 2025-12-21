<?php

namespace App\Helpers;

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
        
        // Check if it's a whole number
        if (floor($number) == $number) {
            return number_format($number, 0);
        }
        
        // Format with max decimals and remove trailing zeros
        $formatted = number_format($number, $maxDecimals);
        
        // Remove trailing zeros after decimal point
        if (strpos($formatted, '.') !== false) {
            $formatted = rtrim($formatted, '0');
            $formatted = rtrim($formatted, '.');
        }
        
        return $formatted;
    }
}
