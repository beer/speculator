#!/usr/bin/php -q
<?php
// :NOTE: 舊資料沒抓
// 本資訊自民國93年2月11日起提供， 民國89年1月4日至93年2月10日資訊由此查詢 
// http://www.twse.com.tw/ch/trading/exchange/MI_INDEX/MI_INDEX_oldtsec.php?input_date=89/01/04&status=1
include(__DIR__ . '/../webdata/init.inc.php');
require_once (LIB_PATH . '/extlibs/simple_html_dom.php');

// hide SQL query
Pix_Table::disableLog(Pix_Table::LOG_QUERY);

$url = 'http://www.twse.com.tw/exchangeReport/MI_INDEX?response=csv&type=MS&date=';

$now = time();
$msg = date("Y/m/d H:i:s", $now) . " run parse-twse-volume\n";
StdLib::log($msg);


$candles = Candle::search("`time` >= " . strtotime("-5 day"));
//$candles = Candle::search("`time` >= " . strtotime("2017/03/28"));

foreach ($candles as $candle) {
    echo '(parse-twse-volume)台指成交量：' . date("Ymd-D", $candle->time) . "\n";
    // 有資料就跳過
    if (!empty($candle->volume)) {
        continue;
    }
    
    $day = date('Ymd', $candle->time);

    $csv_url = $url . $day;
    $csv = file_get_contents($csv_url);
    if (!$csv) {
        echo "(parse-twse-volume)Can't download csv from $csv_url \n";
    } else {
        file_put_contents('twse_volume_' . $day . '.csv', $csv);
        $handle = fopen('twse_volume_' . $day . '.csv', 'r');
        $fp = file('twse_volume_' . $day . '.csv', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $lines = count($fp);
        //echo "{$day}:{$lines}\n";
        if ($lines > 147) {
            echo '(parse-twse-volume)'. date("Ymd-D", $candle->time) . '資料格式改變,行數:' . $lines . PHP_EOL;
            exit;
        }

        $i = 0;
        while (($data = fgetcsv($handle)) !== FALSE) {
            $i++;
            if ($lines == 139 && $i == 128) {
                $volume = $data[1];
                break;
            }
            if ($lines == 143 && $i == 132) {
                $volume = $data[1];
                break;
            }
            if ($lines == 147 && $i == 136) {
                $volume = $data[1];
                break;
            }
        }

        echo "(parse-twse-volume){$day} Volume:" . $volume . PHP_EOL;
        $volume = preg_replace("/([^0-9\\.])/i", "", $volume );
        $candle->volume = $volume;
        $candle->save();
    }
}

$finish_time = time();
$msg = date("Y/m/d H:i:s", $finish_time) . " run parse-twse-volume is finished\n";
StdLib::log($msg);

