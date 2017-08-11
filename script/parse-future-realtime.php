#!/usr/bin/php -q
<?php
include(__DIR__ . '/../webdata/init.inc.php');
require_once (LIB_PATH . '/extlibs/simple_html_dom.php');
//ini_set('default_socket_timeout', 300); // slow server work run solution

// hide SQL query
Pix_Table::disableLog(Pix_Table::LOG_QUERY);

$future_url = 'http://mis.twse.com.tw/stock/data/futures_side.txt?_=';
$taifex_url = 'http://info512.taifex.com.tw/Future/FusaQuote_Norl.aspx';

$now = time();
//$now = time() - 13*60*60;
$open = strtotime(date('Ymd', $now) .'08:45');
$close = strtotime(date('Ymd', $now) .'13:45'); // 最後一筆資料是 13:33 出來
$start = strtotime(date('Ymd', $now) .' 08:35');
$stop = strtotime(date('Ymd', $now) .' 13:55');

$curl = init_curl($taifex_url);

/*{{{*/
while(1) {
    $now = time();
    //$now = time() - 13*60*60;
    echo '(parse-future-realtime)抓指數資訊:' . date('Y/m/d H:i:s', $now) . PHP_EOL;
    if ($now < $start) {
        echo "sleep ". ($start - $now) . ' s'. PHP_EOL;
        sleep($start - $now);
        continue;
    }
    if ($now >= $start and $now <= $stop) {

        $key = intval(microtime(true)*1000);
        $json_url = $future_url . $key;
        $json = file_get_contents($json_url);
        $obj = json_decode($json);
        echo "期貨({$obj->msgArray[0]->t}) high:{$obj->msgArray[0]->h}, low:{$obj->msgArray[0]->l}, close:{$obj->msgArray[0]->z}" . PHP_EOL;

        $row = parser($curl);
        echo "期指({$row['time']}) high:{$row['top']}, low:{$row['low']}, close:{$row['close']}" . PHP_EOL;
       // echo "{$row['label']}({$row['time']}) high:{$row['top']}, low:{$row['low']}, close:{$row['close']}, open:{$row['open']}, volume:{$row['volume']}" . PHP_EOL;

        sleep(5);
    }
    if ($now > $stop) {
        echo "已超過今日開盤時間 ". PHP_EOL;
        break;
    }
}
/*}}}*/

function init_curl($url){
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_COOKIEFILE, "");
    return $curl;
}

function parser($curl) {
    $response = curl_exec($curl);
    $pageHtml = str_get_html($response);

    $row = $pageHtml->find('.custDataGridRow', 2);

    $result = array(
        'label' => trim($row->children(0)->plaintext),
        'status' => trim($row->children(1)->plaintext),
        'buy_price' => $row->children(2)->plaintext,
        'buy_count' => $row->children(3)->plaintext,
        'sell_price' => $row->children(4)->plaintext,
        'sell_count' => $row->children(5)->plaintext,
        'close' => $row->children(6)->plaintext,
        'change' => $row->children(7)->plaintext,
        'percentage' => $row->children(8)->plaintext,
        'volume' => $row->children(9)->plaintext,
        'open' => $row->children(10)->plaintext,
        'top' => $row->children(11)->plaintext,
        'low' => $row->children(12)->plaintext,
        'time' => $row->children(14)->plaintext
    );
    return $result;
}
