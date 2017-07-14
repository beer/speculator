#!/usr/bin/php -q
<?php
include(__DIR__ . '/../webdata/init.inc.php');
require_once (LIB_PATH . '/extlibs/simple_html_dom.php');
ini_set('default_socket_timeout', 300); // slow server work run solution

//網頁查尋網址，在20170524 改版AJAX, 所以改用CSV抓資料
//$url = 'http://www.twse.com.tw/ch/trading/indices/MI_5MINS_HIST/MI_5MINS_HIST.php';
$url = 'http://www.twse.com.tw/indicesReport/MI_5MINS_HIST?response=csv&date=';

$now = time();
// 給日期，csv 檔會給當月每天的指數, :NOTE:但若每月最後一天cron 沒跑到，將lose 最後一天資料
$today = date('Ymd');

$y = date('Y') - 1911;//民國年

$csv_url = $url . $today;
echo '抓指數資訊:' . $csv_url . "\n";
$csv = file_get_contents($csv_url);

if (!$csv) {
    echo "Can't download csv from $csv_url \n";
} else {
    file_put_contents('twse_' . $today . '.csv', $csv);
    $handle = fopen('twse_' . $today . '.csv', 'r');
    $i = 0;
    while (($data = fgetcsv($handle)) !== FALSE) {
        if ($i > 1) { // 前兩行是日期&欄位，直接跳過
            // time, EX: 103/01/05
            $time = $data[0];
            $open = $data[1];
            $top = $data[2];
            $low = $data[3];
            $close = $data[4];

            // year 102 => 2013
            $day = str_replace($y, $y + 1911, $time);
            // for strtotime format
            $day = str_replace('/', '-', $day);
            $day = strtotime($day);

            if (!sizeof(Candle::search(array('time' => $day)))) {
                $row = Candle::createRow();
                $row->time = str_replace(',', '', $day);
                $row->open = str_replace(',', '', $open);
                $row->top = str_replace(',', '', $top);
                $row->low = str_replace(',', '', $low);
                $row->close = str_replace(',', '', $close);
                $row->save();
            }
        }
        $i++;
    }
}
