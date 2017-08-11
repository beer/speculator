#!/usr/bin/php -q
<?php
include(__DIR__ . '/../webdata/init.inc.php');
//require_once (LIB_PATH . '/extlibs/simple_html_dom.php');
//ini_set('default_socket_timeout', 300); // slow server work run solution

// hide SQL query
Pix_Table::disableLog(Pix_Table::LOG_QUERY);

$twse_url = 'http://mis.twse.com.tw/stock/api/getStockInfo.jsp?ex_ch=tse_t00.tw|otc_o00.tw|tse_FRMSA.tw&json=1&delay=0&_=';
$future_url = 'http://mis.twse.com.tw/stock/data/futures_side.txt?_=';
$twse_1min_url = 'http://mis.twse.com.tw/stock/api/getChartOhlcStatis.jsp?ex=tse&ch=t00.tw&fqy=1&_=';

$now = time();
//$now = time() - 13*60*60;
$open = strtotime(date('Ymd', $now) .' 09:00');
$close = strtotime(date('Ymd', $now) .' 13:33'); // 最後一筆資料是 13:33 出來
$start = strtotime(date('Ymd', $now) .' 08:50');
$stop = strtotime(date('Ymd', $now) .' 13:40');
$first_tick_time = strtotime(date('Ymd', $now) .' 09:05');
while(1) {
    $now = time();
    //$now = time() - 13*60*60;
    echo '(parse-twse-realtime)抓指數資訊:' . date('Y/m/d H:i:s', $now) . PHP_EOL;
    if ($now < $start) {
        echo "sleep ". ($start - $now) . ' s'. PHP_EOL;
        sleep($start - $now);
        continue;
    }
    if ($now >= $start and $now <= $stop) {
        $key = intval(microtime(true)*1000);
        $json_url = $twse_url . $key;
        $json = file_get_contents($json_url);
        $obj = json_decode($json);
        $twse = $obj->msgArray[0];
        echo "台指({$twse->t}) open:{$twse->o}, high:{$twse->h}, low:{$twse->l}, close:{$twse->z}, volume:{$twse->v}" . PHP_EOL;

        $key = intval(microtime(true)*1000);
        $json_url = $future_url . $key;
        $json = file_get_contents($json_url);
        $obj = json_decode($json);
        echo "期貨({$obj->msgArray[0]->t}) high:{$obj->msgArray[0]->h}, low:{$obj->msgArray[0]->l}, close:{$obj->msgArray[0]->z}" . PHP_EOL;

        $key = intval(microtime(true)*1000);
        $json_url = $twse_1min_url . $key;
        $json = file_get_contents($json_url);
        $obj = json_decode($json);
        $twse = $obj->infoArray[0];
        $trade = $obj->staticObj;
        echo "台指({$twse->t}) open:{$twse->o}, high:{$twse->h}, low:{$twse->l}, close:{$twse->z}, volume:{$twse->v}" . PHP_EOL;
        echo "委買量:{$trade->t4}, 筆數:{$trade->t2}, 委賣量:{$trade->t3}, 筆數:{$trade->t1}, 成交量:{$trade->tv}, 筆數:{$trade->tr}, 成交金額:{$trade->tz}" . PHP_EOL;

        $candles = $obj->ohlcArray;
        $last_candle = array_pop($candles);
        echo "(" . date('H:i:s', $last_candle->t/1000) ."), close:({$last_candle->c}) volume:{$last_candle->s}" . PHP_EOL;
        echo '----------------------------------------------' . PHP_EOL;

        $tick_time = $real_tick_time = strtotime("{$twse->d} {$twse->t}");
        if ($tick_time >= $close) { // 最後一筆是 13:33:00
            $tick_time = strtotime("{$twse->d} 13:30:00");
        }
        if ($tick_time >= $open and $tick_time <= $close) {
            if ($tick_time == $first_tick_time) { // 補上 9:00 第一筆資料
                $last_tick = Tick::search(1)->order('`time` DESC')->first();
                $tick = Tick::createRow();
                $tick->date = strtotime($twse->d);
                $tick->time = $open;
                $tick->twse = $last_tick->twse;
                $tick->volume = 0;
                $tick->save();

                $volume = TickVolume::createRow();
                $volume->date = strtotime($twse->d);
                $volume->time = $open;
                $volume->save();
            }
            $check = Tick::search("`time` = {$tick_time}");
            if (count($check) < 1) {
                $tick = Tick::createRow();
                $tick->date = strtotime($twse->d);
                $tick->time = $tick_time;
                $tick->twse = $twse->z;
                $tick->volume = $twse->v;
                $tick->save();

                $volume = TickVolume::createRow();
                $volume->date = strtotime($twse->d);
                $volume->time = $tick_time;
                $volume->buy_count = $trade->t2;
                $volume->buy_volume = $trade->t4;
                $volume->sell_count = $trade->t1;
                $volume->sell_volume = $trade->t3;
                $volume->deal_count = $trade->tr;
                $volume->deal_volume = $trade->tv;
                $volume->volume = $trade->tz;
                $volume->save();
            } else {
                $check = $check->first();
            }
            if ($real_tick_time == $close and $check->twse != $twse->z) { // 最後一筆是 13:33:00 為最後收盤價
               $check->twse = $twse->z; 
               $check->volume = $twse->v;
               $check->save();
               $volume = TickVolume::search("`time` = {$tick_time}");
               if (count($volume)) {
                   $volume = $volume->first();
                   $volume->buy_count = $trade->t2;
                   $volume->buy_volume = $trade->t4;
                   $volume->sell_count = $trade->t1;
                   $volume->sell_volume = $trade->t3;
                   $volume->deal_count = $trade->tr;
                   $volume->deal_volume = $trade->tv;
                   $volume->volume = $trade->tz;
                   $volume->save();
               }
            }
        }
        sleep(5);
    }
    if ($now > $stop) {
        echo "已超過今日開盤時間 ". PHP_EOL;
        break;
    }
}
