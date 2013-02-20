#!/usr/bin/php -q
<?php
include(__DIR__ . '/../webdata/init.inc.php');
require_once (LIB_PATH . '/extlibs/simple_html_dom.php');

$url = 'http://www.twse.com.tw/ch/trading/indices/MI_5MINS_HIST/MI_5MINS_HIST.php';

//$years = array(88, 89, 90, 91, 92, 93, 94, 95, 96, 97, 98, 99, 100, 101, 102);
$years = array(102);
$monthes = array('01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12');


foreach ($years as $y) {
    foreach ($monthes as $m) {
        // :NOTE: 未來的跳過
        if (time() < strtotime($y+1911 . "-{$m}")) {
            continue;
        }
        $pageHtml = file_get_html($url . "?myear={$y}&mmon={$m}");

        $trs = $pageHtml->find('.board_trad tr');
        $i = 0;
        foreach ($trs as $tr) {
            $i++;
            if ($i > 2) {
                // time, EX: 103/01/05
                $time = $tr->find('td', 0)->innertext;
                $open = $tr->find('td', 1)->innertext;
                $top = $tr->find('td', 2)->innertext;
                $low = $tr->find('td', 3)->innertext;
                $close = $tr->find('td', 4)->innertext;

                // year 102 => 2013
                $day = str_replace($y, $y + 1911, $time);
                // for strtotime format
                $day = str_replace('/', '-', $day);
                $day = strtotime($day);

                if (!sizeof(Candle::search(array('time' => $day)))) {
                    $row = Candle::createRow();
                    $row->time = str_replace(',', '', $day);
                    $row->open = str_replace(',', '', $open);
                    $row->top = str_replace(',', '', $top);
                    $row->low = str_replace(',', '', $low);
                    $row->close = str_replace(',', '', $close);
                    $row->save();
                }

                //echo $tr->innertext . PHP_EOL;
            }
        }
    }
}
