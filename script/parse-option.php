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
    $rows = OptionTrade::search(
        array(
            'date' => $time, 
            'type' => 'TXO', 
            'user_id' => 1
        ));
    if (count($rows)) {
        continue;
    }

    // 選擇權資料
    $url = 'http://www.taifex.com.tw/chinese/3/7_12_5.asp';/*{{{*/
    $fields = array(
        'DATA_DATE_Y' => date('Y', $time),
        'DATA_DATE_M' => date('n', $time),
        'DATA_DATE_D' => date('j', $time),
        'COMMODITY_ID' => 'TXO',
        'syear' => date('Y', $time),
        'smonth' => date('n', $time),
        'sday' => date('j', $time),
        'datestart' => date('Y/n/j', $time)
    );
    $response = http_post_fields($url, $fields);
    $file = 'taifex-options.html';
    file_put_contents($file, $response);
    $pageHtml = file_get_html($file);

    $table = $pageHtml->find('.table_f');
    echo '權';
    $columns = array(
        'buycall', 'buycall_amount', 'sellcall', 'sellcall_amount', 'calldiff', 'calldiff_amount', 
        'buyput', 'buyput_amount', 'sellput', 'sellput_amount', 'putdiff', 'putdiff_amount');
    $users = array(1, 2, 3, 1, 2, 3); // 1:'自營商', 2:'投信', 3:'外資'
    $trades = $contracts = array();
    $page_time = strtotime(str_replace('/', '-', $pageHtml->find('#datestart', 0)->value));
    if (count($table) and $time == $page_time) {
        echo date('Y/m/d', $time) . 'ok' . PHP_EOL;
        // 3 ~ 9 的 tr 正好是，三大法人
        for ($i = 3; $i < 9; $i++) {
            $user_id = ($i < 6) ? $i - 2 : $i - 5;
            $tds = $pageHtml->find('.table_f tr', $i)->find('td');

            if ($i < 6) {
                $option_trade = OptionTrade::createRow();
                $option_contract = OptionContract::createRow();
                $option_trade->user_id = $user_id;
                $option_contract->user_id = $user_id;
                $option_trade->type = 'TXO';
                $option_contract->type = 'TXO';
                $option_trade->date = $time;
                $option_contract->date = $time;
                $trades[$user_id] = $option_trade;
                $contracts[$user_id] = $option_contract;
            }

            // j : td, 
            $j = $k = 0;
            foreach ($tds as $td) {/*{{{*/
                $j++;
                if (($j > 3 and $i == 3) or ($j > 1 and $i == 6) or (($i != 3) and ($i != 6) and $j > 0)) {
                    //echo $td->innertext . "($j)";

                    if ($j > 3 and $i == 3) { //第一行 買權:自營
                        if ($j == 6) { // 買方契約金額
                            $value = str_replace(',', '', $td->find('div', 1)->innertext);
                            $trades[$user_id]->{$columns[1]} = $value;
                        } else {
                            if ($j == 4) { // 自營
                                continue;
                            } else if ($j&1) { // 異數 5 買方:口數、7 賣方:口數、9 差額:回數、11 買方:口數(未平倉)、13 買方:口數(未平倉)、15  差額:口數(未平倉)
                                $value = str_replace(',', '', $td->find('font', 0)->innertext);
                                $column_id = ($j < 11) ? $j - 5 : $j - 11;
                                if ($j < 11) {
                                    $trades[$user_id]->{$columns[$column_id]} = $value;
                                } else {
                                    $contracts[$user_id]->{$columns[$column_id]} = $value;
                                }
                            } else { // 8 賣方:金額、10 差額:金額、12 買方:金額(未平倉)、14 賣方:金額(未平倉)、16 差額:金額(未平倉)
                                $value = str_replace(',', '', $td->innertext);
                                $column_id = ($j < 12) ? $j -5 : $j - 11;
                                if ($j < 12) {
                                    $trades[$user_id]->{$columns[$column_id]} = $value;
                                } else {
                                    $contracts[$user_id]->{$columns[$column_id]} = $value;
                                }
                            }
                        }
                    } elseif ($i == 6) { // 賣權：自營
                        if ($j == 4) { // 買方契約金額
                            $value = str_replace(',', '', $td->find('div', 1)->innertext);
                            $trades[$user_id]->{$columns[7]} = $value;
                        } else {
                            if ($j == 2) { // 自營
                                continue;
                            } else if ($j&1) { // 異數 3 買方:口數、5 賣方:口數、7 差額:回數、9 買方:口數(未平倉)、11 買方:口數(未平倉)、13 差額:回數(未平倉)
                                $value = str_replace(',', '', $td->find('font', 0)->innertext);
                                $column_id = ($j < 9) ? $j + 3 : $j - 3;
                                if ($j < 9) {
                                    $trades[$user_id]->{$columns[$column_id]} = $value;
                                } else {
                                    $contracts[$user_id]->{$columns[$column_id]} = $value;
                                }
                            } else { // 6 賣方:金額、8 差額:金額、10 買方:金額(未平倉)、12 賣方:金額(未平倉)、14 差額:金額(未平倉)
                                $value = str_replace(',', '', $td->innertext);
                                $column_id = ($j < 10) ? $j + 3 : $j - 3;
                                if ($j < 10) {
                                    $trades[$user_id]->{$columns[$column_id]} = $value;
                                } else {
                                    $contracts[$user_id]->{$columns[$column_id]} = $value;
                                }
                            }
                        }
                    } else { // 其它
                        if ($j == 3) { // 買方契約金額
                            $value = str_replace(',', '', $td->find('div', 1)->innertext);
                            $column_id = ($i < 6) ? 1 : 7;
                            $trades[$user_id]->{$columns[$column_id]} = $value;
                        } else {
                            if ($j == 1) { // 投信、外資、投信、外資
                                continue;
                            } else if ($j&1) { // 異數 5 賣方:金額、7 差額:金額、9 買方:金額(未平倉)、11 賣方:金額(未平倉)、13 差額:金額(未平倉)
                                $value = str_replace(',', '', $td->innertext);
                                if ($i < 6) { // 買權
                                    $column_id = ($j < 9) ? $j - 2 : $j - 8;
                                } else { // 賣權
                                    $column_id = ($j < 9) ? $j + 4 : $j - 2;
                                }
                                if ($j < 9) {
                                    $trades[$user_id]->{$columns[$column_id]} = $value;
                                } else {
                                    $contracts[$user_id]->{$columns[$column_id]} = $value;
                                }
                            } else { // 2 買方:口數、4 賣方:口數、6 差額:回數、8 買方:口數(未平倉)、10 買方:口數(未平倉)、12 差額:回數(未平倉)
                                $value = str_replace(',', '', $td->find('font', 0)->innertext);
                                if ($i < 6) { // 買權
                                    $column_id = ($j < 8) ? $j - 2 : $j - 8;
                                } else { // 賣權
                                    $column_id = ($j < 8) ? $j + 4 : $j - 2;
                                }
                                if ($j < 8) {
                                    $trades[$user_id]->{$columns[$column_id]} = $value;
                                } else {
                                    $contracts[$user_id]->{$columns[$column_id]} = $value;
                                }
                            }
                        }
                    }
                    echo $value . ', ';
                }
            }/*}}}*/
            echo PHP_EOL;
            if ($i == 5) {
                echo PHP_EOL;
            }
        }
        if (sizeof($trades)) {
            for ($i = 1 ; $i < 4; $i++) {
                $trades[$i]->save();
                $contracts[$i]->save();
                //var_dump($contracts[$i]->toArray());
                //var_dump($trades[$i]->toArray());
            }
        }
        rename($file, 'taifex-options-' . date('Y-m-d', $time) . '.html');
    } else {
        echo date('Y/m/d', $time) . 'no' . PHP_EOL;
    }
    /*}}}*/
}
