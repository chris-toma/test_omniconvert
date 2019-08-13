<?php


namespace App\Helpers;

/**
 * Class QueryHelper
 * @package App\Helpers
 */
class QueryHelper
{
    const PERIOD_TYPE_ALL_TIME = 1;
    const PERIOD_TYPE_LAST_WEEK = 2;
    const PERIOD_TYPE_LAST_MONTH = 3;

    /**
     * @param int $type
     * @return string
     */
    public static function dateIntervalConditionDiscerner(int $type)
    {
        switch ($type) {
            case self::PERIOD_TYPE_LAST_WEEK:
                $start = date("Y-m-d", strtotime("last week monday"));
                $end = date("Y-m-d", strtotime("last week sunday"));
                break;
            case self::PERIOD_TYPE_LAST_MONTH:
                $start = date("Y-m-01", strtotime("last month"));
                $end = date("Y-m-t", strtotime("last month"));
                break;
        }
        return ($type == self::PERIOD_TYPE_LAST_WEEK || $type == self::PERIOD_TYPE_LAST_MONTH)
            ? "WHERE created_at BETWEEN '{$start}' AND '{$end}'"
            : "";
    }
}