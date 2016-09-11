#!/usr/bin/php -q
<?php
// :NOTE: 舊資料沒抓
// 本資訊自民國93年2月11日起提供， 民國89年1月4日至93年2月10日資訊由此查詢 
// http://www.twse.com.tw/ch/trading/exchange/MI_INDEX/MI_INDEX_oldtsec.php?input_date=89/01/04&status=1
include(__DIR__ . '/../webdata/init.inc.php');
require_once (LIB_PATH . '/extlibs/simple_html_dom.php');

$start_date = '2015-09-10';
$candles = Candle::search("`time` >= " . strtotime($start_date));

foreach ($candles as $candle) {
    //echo date("Ymd", $d->time) . "\n";
    // 有資料就跳過
    if (!empty($candle->volume)) {
        continue;
    }
    
    $y = date('Y', $candle->time) - 1911;
    $m = date('m', $candle->time);
    $d = date('d', $candle->time);
    $file = 'twse-volume.html';
    $pass_check = false;
    $qdate = "{$y}/{$m}/{$d}";

    $url = 'http://www.twse.com.tw/ch/trading/exchange/MI_INDEX/MI_INDEX.php';
    $fields = array(
        'qdate' => $qdate
    );

    $response = http_post_fields($url, $fields);
    file_put_contents($file, $response);
    $pageHtml = file_get_html($file);
    $trs = $pageHtml->find('table tr');

    if (count($trs)  == 16) {
        $volume = $trs[15]->find('td', 1)->plaintext;
        echo "{$qdate} Volume:" . $volume;
    } elseif (count($trs) == 92) {
        $volume = $trs[91]->find('td', 1)->plaintext;
    } elseif (count($trs) == 94 or count($trs) == 101) {
        $volume = $trs[93]->find('td', 1)->plaintext;
    } elseif (count($trs) == 106) {
        $volume = $trs[98]->find('td', 1)->plaintext;
    } elseif (count($trs) == 108) {
        $volume = $trs[100]->find('td', 1)->plaintext;
    } elseif (count($trs) == 110) {
        $volume = $trs[102]->find('td', 1)->plaintext;
    } elseif (count($trs) == 112) {
        $volume = $trs[104]->find('td', 1)->plaintext;
    } elseif (count($trs) == 114) {
        $volume = $trs[106]->find('td', 1)->plaintext;
    } elseif (count($trs) == 116) {
        $volume = $trs[108]->find('td', 1)->plaintext;
    } elseif (count($trs) == 118) {
        $volume = $trs[110]->find('td', 1)->plaintext;
    } elseif (count($trs) == 120) {
        $volume = $trs[112]->find('td', 1)->plaintext;
    } elseif (count($trs) == 122) {
        $volume = $trs[114]->find('td', 1)->plaintext;
    } elseif (count($trs) == 124) {
        $volume = $trs[116]->find('td', 1)->plaintext;
    } else {
        echo count($trs) . PHP_EOL;
        $volume = $trs[114]->find('td', 0)->plaintext;
        echo "{$qdate} Volume:" . $volume . PHP_EOL;
        $error_msg = date("Ymd", $candle->time) . "資料格式不符\n";
        echo $error_msg;
        throw new Exception($error_msg);
    }
    echo "{$qdate} Volume:" . $volume;

    $volume = preg_replace("/([^0-9\\.])/i", "", $volume );
    $candle->volume = $volume;
    $candle->save();
}
