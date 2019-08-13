<?php
namespace App\Helpers;

/**
 * Class DateHelper
 * @package App\Helpers
 */
class DateHelper
{
    /**
     * @param string $start
     * @param string $end
     * @return false|string
     */
    public static function randomDateInRange($start, $end)
    {
        $min = strtotime($start);
        $max = strtotime($end);
        // Generate random number using above bounds
        $val = rand($min, $max);
        // Convert back to desired date format
        return date('Y-m-d', $val);
    }
}