<?php
class StdLib
{
    public static function getClearance($diff = 0, $timestamp = null)
    {
        $now = $timestamp ? $timestamp : time();
        $year = date('Y', $now);
        $month = date('m', $now);

        return strtotime("third Wednesday", mktime(0, 0, 0, $month + $diff, 0, $year));
    }
}
