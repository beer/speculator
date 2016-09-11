#!/usr/bin/php -q
<?php
include(__DIR__ . '/../webdata/init.inc.php');
require_once (LIB_PATH . '/extlibs/simple_html_dom.php');

//$candles = Candle::search("`time` < " . strtotime('2004-10-15') . " AND `time` >= " . strtotime('2004-03-19'));
$candles = Candle::search("`time` >= " . strtotime("+1 week"));

foreach ($candles as $candle) {
    //echo date("Ymd", $d->time) . "\n";
    // 有資料就跳過
    if (!empty($candle->frequency)) {
        continue;
    }
    
    //echo date("Ymd", $candle->time) . "\n";
    $y = date('Y', $candle->time) - 1911;
    $m = date('m', $candle->time);
    $d = date('d', $candle->time);
    $file = 'twse-ticks.html';
    $pass_check = false;
    
    // 2000/1/4 ~ 2004/10/14 資料抓取
    if ($candle->time < strtotime('2004-10-15')) {
        $url = "http://www.twse.com.tw/ch/trading/exchange/MI_5MINS_INDEX/MI_5MINS_INDEX_oldtsec.php";
        $fields = array(
            'input_date' => "{$y}/{$m}/{$d}",
        );

        $response = http_post_fields($url, $fields);
        file_put_contents($file, $response);
        $pageHtml = file_get_html($file);
        $title = $pageHtml->find('table tr th', 0)->plaintext;
        //$title = $pageHtml->find('h2', 0)->children(0)->plaintext;
        // 舊的網頁是 Big5 
        $title = iconv("big5","UTF-8",$title); 
        $trs = $pageHtml->find('table', 2)->find('tr');

        if ($title == '時間') { //沒有th title ex:2000/01/04
            $trs = array_slice($trs, 1);
            $pass_check = true;
        } else { // ex:2000/01/06
            $trs = array_slice($trs, 2);
        }
        echo $trs[0]->find('td', 0)->plaintext . PHP_EOL;
        /*
        foreach ($trs as $tr) {
            $time = $tr->find('td', 0)->plaintext;
            $twse = $tr->find('td', 1)->plaintext;
            echo "$time : $twse \n";
        }
         */
    } else { // 2004/10/15 ~ 資料抓取
        $url = "http://www.twse.com.tw/ch/trading/exchange/MI_5MINS_INDEX/MI_5MINS_INDEX.php";
        $fields = array(
            'qdate' => "{$y}/{$m}/{$d}",
        );

        $response = http_post_fields($url, $fields);
        file_put_contents($file, $response);
        $pageHtml = file_get_html($file);
        $title = $pageHtml->find('table tr td', 0)->plaintext;

        $trs = $pageHtml->find('table', 0)->find('tr');
        $trs = array_slice($trs, 2);
    }

    // 確認抓的資料日期正確
    // 89年01月17日每五分鐘指數
    $pattern = '/^(?P<year>\d+)年(?P<month>\d+)月(?P<day>\d+)日每(?P<frequency>(一分鐘|五分鐘|5|10|15))(秒)?指數*/';
    preg_match($pattern, $title, $matches);

    if ($pass_check or ($candle->time == strtotime($matches['year'] + 1911 . "-{$matches['month']}-{$matches['day']}")) ) {
        foreach ($trs as $tr) {
            $time = $tr->find('td', 0)->plaintext;
            $twse = $tr->find('td', 1)->plaintext;
            $twse = preg_replace("/([^0-9\\.])/i", "", $twse );

            $tick = Tick::createRow();
            $tick->date = $candle->time;
            $day_time = $y + 1911 . "-$m-$d {$time}";
            $tick->time = strtotime($y + 1911 . "-$m-$d {$time}");
            $tick->twse = $twse;

            $tick->save();
        }

        // 在candle 加上tick 的頻率
        $candle->frequency = ($pass_check or in_array($matches['frequency'], array('一分鐘', '五分鐘')) )? 60 : $matches['frequency'];
        $candle->save();
        echo date("Ymd", $candle->time) . "\n";
        echo $title . PHP_EOL;
    } else {
        echo $candle->time . ' == ' . strtotime($matches['year'] + 1911 . "-{$matches['month']}-{$matches['day']}") . ' ??  ' . PHP_EOL;
        $error_msg = date("Ymd", $candle->time) . "內容抓取不對:{$title}\n";
        echo $error_msg;
        throw new Exception($error_msg);
    }
    //echo count($dates);
}
