<?php

namespace App\Helpers;

use Andegna\DateTime;
use Andegna\Constants;

class EthiopianDateHelper
{
    /**
     * Get the current Ethiopian date formatted.
     * 
     * @param string $format
     * @return string
     */
    public static function now($format = Constants::DATE_ETHIOPIAN_ORTHODOX)
    {
        $ethiopianDate = new DateTime();
        return $ethiopianDate->format($format);
    }
    
    /**
     * Convert Gregorian to Ethiopian.
     * 
     * @param \DateTime $date
     * @return DateTime
     */
    public static function fromGregorian($date)
    {
        return new DateTime($date);
    }

    /**
     * Get array of date parts (DayName, MonthName, Day, Year) in Amharic
     */
    public static function getCurrentParts() {
        $date = new DateTime();
        // Custom formatting can be done here if the library's constants aren't enough
        // but Andegna is pretty good with default formatting.
        return [
            'day_name' => $date->format('l'),
            'day' => $date->format('d'),
            'month' => $date->format('F'),
            'year' => $date->format('Y'),
            'full' => $date->format(Constants::DATE_GEEZ), // Or DATE_ETHIOPIAN_ORTHODOX
        ];
    }
}
