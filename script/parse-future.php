#!/usr/bin/php -q
<?php
include(__DIR__ . '/../webdata/init.inc.php');
require_once (LIB_PATH . '/extlibs/simple_html_dom.php');
$now = time();
$today = date('Y-m-d', $now);
//期交所只提供三年前的資料
//$last_record_year = date('Y', $now) - 3;
$last_record_year = date('Y', $now);
// 抓資料只確認一個月前的的
$last_record_month = date('m', $now) - 1;
//$last_record_month = date('m', $now);
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
    if (count($rows)) {
        continue;
    }

    // 期貨
    $url = 'http://www.taifex.com.tw/chinese/3/7_12_3.asp';/*{{{*/
    $fields = array(
        'DATA_DATE_Y' => date('Y', $time),
        'DATA_DATE_M' => date('n', $time),
        'DATA_DATE_D' => date('j', $time),
        'COMMODITY_ID' => 'TXF',
        'syear' => date('Y', $time),
        'smonth' => date('n', $time),
        'sday' => date('j', $time),
        'datestart' => date('Y/n/j', $time)
    );


    $response = http_post_fields($url, $fields);
    $file = 'taifex-futures.html';
    file_put_contents($file, $response);
    $pageHtml = file_get_html($file);

    $table = $pageHtml->find('.table_f');
    echo '期';
    $columns = array('buy', 'buy_amount', 'sell', 'sell_amount', 'diff', 'diff_amount', 'buy', 'buy_amount', 'sell', 'sell_amount', 'diff', 'diff_amount',);
    $users = array('', '自營商', '投信', '外資');
    if (count($table)) {
        echo date('Y/m/d', $time) . 'ok' . PHP_EOL;
        // 3 ~ 6 的 tr 正好是，三大法人
        for ($i = 3; $i < 6; $i++) {
            $tds = $pageHtml->find('.table_f tr', $i)->find('td');
            $j = $k = 0;
            $future_trade = FutureTrade::createRow();
            $future_contract = FutureContract::createRow();
            foreach ($tds as $td) {
                $j++;
                if (($j > 3 and $i == 3) or ($i > 3 and $j > 1)) {
                    if ($j&1) {
                        $value = str_replace(',', '', $td->find('div', 0)->innertext);
                    } else {
                        $value = str_replace(',', '', $td->find('font', 0)->innertext);
                    }
                    if ($k < 6) {
                        $future_trade->{$columns[$k]} = $value;
                    } else {
                        $future_contract->{$columns[$k]} = $value;
                    }
                    $k++;
                    if ($k == 6) {
                        // $i - 2 正好是user_id;
                        $future_trade->user_id = $i - 2;
                        $future_trade->date = $time;
                        $future_trade->type = 'TXF';
                        $future_trade->save();
                    }
                    if ($k == 12) {
                        // $i - 2 正好是user_id;
                        $future_contract->user_id = $i - 2;
                        $future_contract->date = $time;
                        $future_contract->type = 'TXF';
                        $future_contract->save();
                    }
                }
            }
            echo PHP_EOL;
        }
    } else {
        echo date('Y/m/d', $time) . 'no' . PHP_EOL;
    }
    /*}}}*/
}
