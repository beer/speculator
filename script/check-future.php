<?php
include(__DIR__ . '/../webdata/init.inc.php');
require_once (LIB_PATH . '/extlibs/simple_html_dom.php');
$now = time();
$today = date('Y-m-d', $now);
//期交所只提供三年前的資料
$last_record_year = date('Y', $now) - 3;
//$last_record_year = date('Y', $now);
// 抓資料只確認一個月前的的
//$last_record_month = date('m', $now) - 1;
$last_record_month = date('m', $now);
$last_record_day = date('d', $now);
$last_record_timestamp = mktime(0, 0, 0, $last_record_month, $last_record_day, $last_record_year);
//echo date('Y/m/d', $last_record_timestamp);

for ($time = $last_record_timestamp ; $time < $now ; $time += 86400) {
    
    // 檢查是否已有資料
    $rows = FutureTrade::search(
        array(
            'date' => $time, 
            'type' => 'TXF', 
            'user_id' => 1
        ));
    if (count($rows) > 1) {
        echo sizeof($rows);
    }
}
