#!/usr/bin/php -q
<?php
include(__DIR__ . '/../webdata/init.inc.php');
require_once (LIB_PATH . '/extlibs/simple_html_dom.php');
ini_set('default_socket_timeout', 300); // slow server work run solution

$url = 'http://www.twse.com.tw/exchangeReport/MI_5MINS_INDEX?response=csv&date=';

//$candles = Candle::search("`time` < " . strtotime('2004-10-15') . " AND `time` >= " . strtotime('2004-03-19'));
$candles = Candle::search("`time` >= " . strtotime("-1 week"));
//$candles = Candle::search("`time` >= " . strtotime('2017-03-02'));

foreach ($candles as $candle) {
    //echo date("Ymd", $d->time) . "\n";
    // 有資料就跳過
    if (!empty($candle->frequency)) {
        continue;
    }
    
    $day = date('Ymd', $candle->time);
    $csv_url = $url . $day;
    $csv = file_get_contents($csv_url);

    if (!$csv) {
        echo "Can't download csv from $csv_url \n";
    } else {
        file_put_contents('twse_5min_' . $day . '.csv', $csv);
        $handle = fopen('twse_5min_' . $day . '.csv', 'r');
        $fp = file('twse_5min_' . $day . '.csv', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $lines = count($fp);
        echo "{$day}:{$lines}\n";

        $i = 0;
        while (($data = fgetcsv($handle)) !== FALSE) {
            if ($i > 1 and $i < 3243) { // 前兩行是日期&欄位，直接跳過, 後面文字說明也跳過
                $time = $data[0];
                // 移掉, 
                $twse = preg_replace("/([^0-9\\.])/i", "", $data[1]);
                // 來源資料格式有問題，時間為="09:00:00", 需拿掉 = & "
                $time = preg_replace('/=|\"/', "", $time);

                $tick = Tick::createRow();
                $tick->date = $candle->time;
                $day_time = "$day {$time}";
                $tick->time = strtotime($day_time);
                $tick->twse = $twse;

                $tick->save();
            }
            $i++;
        }
        // 在candle 加上tick 的頻率
        echo date('Ymd (D)', $candle->time);
        $candle->frequency = 5;
        $candle->save();
    }
}
