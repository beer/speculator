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

    public static function log($msg, $file = "cron_log.txt")
    {
        file_put_contents($file, $msg, FILE_APPEND | LOCK_EX);
    }
}
