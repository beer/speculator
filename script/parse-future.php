#!/usr/bin/php -q
<?php
include(__DIR__ . '/../webdata/init.inc.php');
require_once (LIB_PATH . '/extlibs/simple_html_dom.php');
$now = time();
$today = date('Y-m-d', $now);
//期交所只提供三年前的資料
//$last_record_year = date('Y', $now) - 3;
$last_record_year = date('Y', $now);
$last_record_month = date('m', $now);
// 抓資料只確認前三天資料
$last_record_day = date('d', $now) - 3;
$last_record_timestamp = mktime(0, 0, 0, $last_record_month, $last_record_day, $last_record_year);

for ($time = $last_record_timestamp ; $time < $now ; $time += 86400) {
    echo '期貨盤後：' . date('Y/m/d (D)', $time) . PHP_EOL;
    
    // 檢查是否已有資料
    $rows = FutureTrade::search(
        array(
            'date' => $time, 
            'type' => 'TXF', 
            'user_id' => 1
        ));
    $rows2 = FutureContract::search(
        array(
            'date' => $time, 
            'type' => 'TXF', 
            'user_id' => 1
        ));
    if (count($rows) and count($rows2)) {
        continue;
    }

    // 期貨
    #$url = 'http://www.taifex.com.tw/chinese/3/7_12_3.asp';/*{{{*/
    $url = 'http://60.250.19.171/chinese/3/7_12_3.asp';/*{{{*/
    $fields = array(
        'goday' => '',
        'DATA_DATE_Y' => date('Y', $time),
        'DATA_DATE_M' => date('m', $time),
        'DATA_DATE_D' => date('d', $time),
        'COMMODITY_ID' => 'TXF',
        'syear' => date('Y', $time),
        'smonth' => date('m', $time),
        'sday' => date('d', $time),
        'datestart' => date('Y/m/d', $time),
        'COMMODITY_ID' => ''
    );

    $response = Helper::http_post_fields($url, $fields);
    $pageHtml = str_get_html($response);

    $table = $pageHtml->find('.table_f');
    $columns = array('buy', 'buy_amount', 'sell', 'sell_amount', 'diff', 'diff_amount', 'buy', 'buy_amount', 'sell', 'sell_amount', 'diff', 'diff_amount',);
    $users = array('', '自營商', '投信', '外資');
    $page_time = strtotime(str_replace('/', '-', $pageHtml->find('#datestart', 0)->value));
    echo '抓取資料日期：' . $pageHtml->find('#datestart', 0)->value;
    if (count($table) and $time == $page_time) {
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
        echo ' 休盤日或資料日期不正確' . PHP_EOL;
    }
    /*}}}*/
}
