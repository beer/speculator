#!/usr/bin/php -q
<?php
include(__DIR__ . '/../webdata/init.inc.php');
require_once (LIB_PATH . '/extlibs/simple_html_dom.php');
ini_set('default_socket_timeout', 300); // slow server work run solution

// hide SQL query
Pix_Table::disableLog(Pix_Table::LOG_QUERY);

$url = 'http://www.tse.com.tw/exchangeReport/MI_5MINS?response=csv&date=';

// 1分鐘tick
//$candles = Candle::search("`time` >= " . strtotime('2011-01-15') . " AND `time` <= " . strtotime('2011-01-14'));
// 15秒tick
//$candles = Candle::search("`time` >= " . strtotime('2011-01-17') . " AND `time` <= " . strtotime('2014-02-23'));
// 10秒tick
//$candles = Candle::search("`time` >= " . strtotime('2014-02-24') . " AND `time` <= " . strtotime('2014-12-27'));
// 5秒tick
//$candles = Candle::search("`time` >= " . strtotime('2014-12-29'));
$candles = Candle::search("`time` >= " . strtotime("-3 day"));

foreach ($candles as $candle) {
    echo '(parse-twse-5min-volume)台指Tick Volume 資料：' . date("Ymd-D", $candle->time) . " 資料區間:{$candle->frequency}\n";

    // 已有資料就跳過
    $check = TickVolume::search('`date` = ' . $candle->time);
    if (count($check) == 3241) { // 5 sec tick 1天要有 3241 資料
        continue;
    }
    
    $day = date('Ymd', $candle->time);
    $csv_url = $url . $day;
    $csv = file_get_contents($csv_url);

    if (!$csv) {
        echo "(parse-twse-5min-index)Can't download csv from $csv_url \n";
    } else {
        file_put_contents('twse_5min_volume_' . $day . '.csv', $csv);
        $handle = fopen('twse_5min_volume_' . $day . '.csv', 'r');
        $fp = file('twse_5min_volume_' . $day . '.csv', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $i = 0;
        $create_ticks = $update_ticks = 0;
        while (($data = fgetcsv($handle)) !== FALSE) {
            $i++;
            if ($i > 2 and $i < 3244) { // 前兩行是日期&欄位，直接跳過, 後面文字說明也跳過
                $time = $data[0];
                // 移掉, 
                // 來源資料格式有問題，時間為="09:00:00", 需拿掉 = & "
                $time = preg_replace('/=|\"/', "", $time);
                $day_time = strtotime("$day {$time}");
                $buy_count = preg_replace("/([^0-9\\.])/i", "", $data[1]);
                $volume = preg_replace("/([^0-9\\.])/i", "", $data[7]);


                $check = TickVolume::search("`time` = {$day_time}");
                if (count($check) < 1) {
                    $tick = TickVolume::createRow();
                    $tick->date = $candle->time;
                    $tick->time = $day_time;
                    $tick->buy_count = preg_replace("/([^0-9\\.])/i", "", $data[1]);
                    $tick->buy_volume = preg_replace("/([^0-9\\.])/i", "", $data[2]);
                    $tick->sell_count = preg_replace("/([^0-9\\.])/i", "", $data[3]);
                    $tick->sell_volume = preg_replace("/([^0-9\\.])/i", "", $data[4]);
                    $tick->deal_count = preg_replace("/([^0-9\\.])/i", "", $data[5]);
                    $tick->deal_volume = preg_replace("/([^0-9\\.])/i", "", $data[6]);
                    $tick->volume = $volume;
                    $tick->save();
                    echo "(parse-twse-5min-volume)" . date('Y/m/d H:i:s', $day_time) . "({$day_time}) created" . PHP_EOL;
                    $create_ticks++;
                } else {
                    $check = $check->first();
                    $volume = preg_replace("/([^0-9\\.])/i", "", $data[7]);
                    if ($buy_count != $check->buy_count) { // 要用buy_count 判斷，因為開盤第一筆資料 volume 為 0
                        $check->buy_count = preg_replace("/([^0-9\\.])/i", "", $data[1]);
                        $check->buy_volume = preg_replace("/([^0-9\\.])/i", "", $data[2]);
                        $check->sell_count = preg_replace("/([^0-9\\.])/i", "", $data[3]);
                        $check->sell_volume = preg_replace("/([^0-9\\.])/i", "", $data[4]);
                        $check->deal_count = preg_replace("/([^0-9\\.])/i", "", $data[5]);
                        $check->deal_volume = preg_replace("/([^0-9\\.])/i", "", $data[6]);
                        $check->volume = $volume;
                        $check->save();
                        echo "(parse-twse-5min-volume)" . date('Y/m/d H:i:s', $day_time) . "({$day_time}) {$check->buy_count} -> {$buy_count}" . PHP_EOL;
                        $update_ticks++;
                    }

                }

            }
        }
        echo "(parse-twse-5min-volume) created {$create_ticks} ticks, updated {$update_ticks} ticks" . PHP_EOL;
    }
}
