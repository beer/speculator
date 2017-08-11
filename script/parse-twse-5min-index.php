#!/usr/bin/php -q
<?php
include(__DIR__ . '/../webdata/init.inc.php');
require_once (LIB_PATH . '/extlibs/simple_html_dom.php');
ini_set('default_socket_timeout', 300); // slow server work run solution

// hide SQL query
Pix_Table::disableLog(Pix_Table::LOG_QUERY);

$url = 'http://www.twse.com.tw/exchangeReport/MI_5MINS_INDEX?response=csv&date=';

//$candles = Candle::search("`time` < " . strtotime('2004-10-15') . " AND `time` >= " . strtotime('2004-03-19'));
$candles = Candle::search("`time` >= " . strtotime("-3 day"));
//$candles = Candle::search("`time` >= " . strtotime('2017-03-02'));

foreach ($candles as $candle) {
    echo '(parse-twse-5min-index)台指Tick資料：' . date("Ymd-D", $candle->time) . "\n";
    // 有資料就跳過
    if (!empty($candle->frequency)) {
        continue;
    }
    
    $day = date('Ymd', $candle->time);
    $csv_url = $url . $day;
    $csv = file_get_contents($csv_url);

    if (!$csv) {
        echo "(parse-twse-5min-index)Can't download csv from $csv_url \n";
    } else {
        file_put_contents('twse_5min_' . $day . '.csv', $csv);
        $handle = fopen('twse_5min_' . $day . '.csv', 'r');
        $fp = file('twse_5min_' . $day . '.csv', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        $i = 0;
        $create_ticks = $update_ticks = 0;
        while (($data = fgetcsv($handle)) !== FALSE) {
            if ($i > 1 and $i < 3243) { // 前兩行是日期&欄位，直接跳過, 後面文字說明也跳過
                $time = $data[0];
                // 移掉, 
                $twse = preg_replace("/([^0-9\\.])/i", "", $data[1]);
                // 來源資料格式有問題，時間為="09:00:00", 需拿掉 = & "
                $time = preg_replace('/=|\"/', "", $time);
                $day_time = strtotime("$day {$time}");

                $check = Tick::search("`time` = {$day_time}");
                if (count($check) < 1) {
                    $tick = Tick::createRow();
                    $tick->date = $candle->time;
                    $tick->time = $day_time;
                    $tick->twse = $twse;
                    $tick->save();
                    echo "(parse-twse-5min-index)" . date('Y/m/d H:i:s', $day_time) . "({$day_time}) twse -> {$twse}" . PHP_EOL;
                    $create_ticks++;
                } else {
                    $check = $check->first();
                    if ($twse != $check->twse) {
                        $check->twse = $twse;
                        $check->save();
                        echo "(parse-twse-5min-index)" . date('Y/m/d H:i:s', $day_time) . "({$day_time}) {$check->twse} -> {$twse}" . PHP_EOL;
                        $update_ticks++;
                    }
                }
            }
            $i++;
        }
        echo "(parse-twse-5min-index) created {$create_ticks} ticks, updated {$update_ticks} ticks" . PHP_EOL;
        // 在candle 加上tick 的頻率
        $candle->frequency = 5;
        $candle->save();
    }
}
