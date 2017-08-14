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
$start = strtotime(date('Ymd', $now) .' 08:25');
$stop = strtotime(date('Ymd', $now) .' 13:55');
$day = strtotime(date('Ymd', $now));

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

        // 來源1: http://mis.twse.com.tw/stock/index.jsp
        $key = intval(microtime(true)*1000);
        $json_url = $future_url . $key;
        $json = file_get_contents($json_url);
        $obj = json_decode($json);
        echo "{$obj->msgArray[0]->c}({$obj->msgArray[0]->t}) high:{$obj->msgArray[0]->h}, low:{$obj->msgArray[0]->l}, close:{$obj->msgArray[0]->z}" . PHP_EOL;

        $day_time = strtotime($obj->msgArray[0]->d . ' ' . $obj->msgArray[0]->t);
        $check = FutureTick::search("`time` = {$day_time} AND `label` = '{$obj->msgArray[0]->c}'");
        if (count($check) < 1 and $obj->msgArray[0]->z) {
            $tick = FutureTick::createRow();
            $tick->date = strtotime($obj->msgArray[0]->d);
            $tick->time = $day_time;
            $tick->label = $obj->msgArray[0]->c;
            $tick->top = $obj->msgArray[0]->h;
            $tick->low = $obj->msgArray[0]->l;
            $tick->close = $obj->msgArray[0]->z;
            $tick->change = $obj->msgArray[0]->z - $obj->msgArray[0]->y;
            $tick->ex_close = $obj->msgArray[0]->y;
            $tick->save();
        }
        
        // 來源2: 抓期交所 5sec 資料 http://www.taifex.com.tw/chinese/3/dl_3_1_2.asp
        $futures = parser($curl);
        
        foreach ($futures as $f) {
            echo "{$f['label']}({$f['time']}) high:{$f['top']}, low:{$f['low']}, close:{$f['close']}" . PHP_EOL;

            $day_time = strtotime(date('Ymd', $now) . ' ' . $f['time']);
            $check = FutureTick::search("`time` = {$day_time} AND `label` = '{$f['label']}'");

            if (count($check) < 1) {
                $tick = FutureTick::createRow();
            } else {
                $tick = $check->first();
                if ($tick->volume == $f['volume']) {
                    continue;
                }
            }
            $tick->date = $day;
            $tick->time = $day_time;
            $tick->label = $f['label'];
            $tick->open = $f['open'];
            $tick->top = $f['top'];
            $tick->low = $f['low'];
            $tick->close = $f['close'];
            $tick->bid = $f['bid'];
            $tick->bid_count = $f['bid_count'];
            $tick->ask = $f['ask'];
            $tick->ask_count = $f['ask_count'];
            $tick->change = $f['change'];
            $tick->amplitude = $f['amplitude'];
            $tick->volume = $f['volume'];
            $tick->ex_close = $f['ex_close'];
            $tick->save();

        }
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
    $result = array();

    // 第2筆資料是大台近，第25筆是小台近
    foreach (array(2, 25) as $k => $v) {
        $row = $pageHtml->find('.custDataGridRow', $v);
        $label = ($v == 2 ? 'TX' : 'MTX') . substr(trim($row->children(0)->plaintext), -3);
        // 移掉 ,
        $regex = "/([^0-9\\.])/i";
        $result[] = array(
            'label' => $label,
            'status' => trim($row->children(1)->plaintext),
            'bid' => preg_replace($regex, '', $row->children(2)->plaintext),
            'bid_count' => preg_replace($regex, '',$row->children(3)->plaintext),
            'ask' => preg_replace($regex, '',$row->children(4)->plaintext),
            'ask_count' => preg_replace($regex, '',$row->children(5)->plaintext),
            'close' => preg_replace($regex, '',$row->children(6)->plaintext),
            'change' => $row->children(7)->plaintext,
            'amplitude' => $row->children(8)->plaintext,
            'volume' => preg_replace($regex, '',$row->children(9)->plaintext),
            'open' => preg_replace($regex, '',$row->children(10)->plaintext),
            'top' => preg_replace($regex, '',$row->children(11)->plaintext),
            'low' => preg_replace($regex, '',$row->children(12)->plaintext),
            'ex_close' => preg_replace($regex, '',$row->children(13)->plaintext),
            'time' => preg_replace($regex, '',$row->children(14)->plaintext)
        );
    }
    return $result;
}
