<?php
class AjaxController extends Pix_Controller
{
    public function twiiAction()
    {
        $candles = Candle::search(1)->order('time ASC');
        $data = array();
        foreach ($candles as $c) {
            // UTC+8
            $data[] = array($c->time * 1000 + 28800*1000, (float) $c->open, (float) $c->top, (float) $c->low, (float) $c->close);
        }
        $this->json($data);
    }

    public function getDayInfoAction()
    {
        $day = strtotime($_POST['day']);
        $v = $this->view;
        $v->day = $day;

        $content = $this->view->partial('ajax/dayinfo.phtml', $v);
        $data = array('error' => 0, 'content' => $content);
        $this->json($data);
    }

    public function d3candleAction()
    {
        //$candles = Candle::search(1)->order('time ASC')->limit(200);
        $candles = Candle::search('`time` > ' . strtotime('2016-06-01'))->order('time ASC');
        $data = array();
        foreach ($candles as $c) {
            // UTC+8
            // volume 單位用(億)表示
            $data[] = array(date('d-M-y', $c->time), (float) $c->open, (float) $c->top, (float) $c->low, (float) $c->close, (float) $c->volume / 100000000);
        }
        $this->json($data);
    }

    public function feedAction()
    {
        $periodicity = 30*60; //秒為單位
        $start = strtotime('2017-08-03');
        $end = strtotime('2017-08-04 09:35');

        $open_time = '09:00'; //每日開盤時間
        $close_time = '13:30'; //每日收盤時間

        $ticks = Tick::search("`time` >= {$start} AND `time` <= {$end}")->order('time ASC');
        $length = count($ticks);

        $total_candle = intval($length / ($periodicity / 5));

        $data = array();

        // 將tick 資料轉為 candle 
        $row = array();
        $pool = array();

        $i = 0;
        foreach ($ticks as $tick) {
            $i++;
            $current_date_close_time = strtotime(date('Y/m/d', $tick->date) . " $close_time");
            $current_date_open_time = strtotime(date('Y/m/d', $tick->date) . " $open_time");
            
            // :NOTE:因為 tick 資料 9:00 那筆期時是前一天的收盤價，在candle中要忽略，不然會看不到跳空, 所以若要可以指定到秒的話就要多做判斷
            // 第二筆tick 資料，才是日tick的第一筆
            if ($i == $real_first_tick) {
                $row['open'] = $tick->twse;
                $pool[] = $tick->twse;
            }

            if ($tick->time % $periodicity == 0) {
                // 第一筆資料且資料長度不只一筆
                if ($tick->time == $current_date_open_time and $length > 1) {
                    $row['time'] = date('Y/m/d H:i:s', $tick->time);
                    $real_first_tick = $i + 1;
                    continue;
                }

                // 結算週期資料，轉為candle, 並清空
                if (count($pool)) {
                    $row['top'] = max($pool);
                    $row['low'] = min($pool);
                    $row['close'] = array_pop($pool);
                    $row['volume'] = rand(10, 1000);
                    $data[] = array($row['time'], (float) $row['open'], (float) $row['top'], (float) $row['low'], (float) $row['close'], (float) $row['volume']);
                    $pool = $row = array();

                    // 如果是當日最後一筆資料，需要將資料加到上一個candle
                    if ($tick->time == $current_date_close_time) {
                        echo $current_date_close_time . PHP_EOL;
                        $last_index = count($data) - 1;
                        $data[$last_index][2] = (float) max($data[$last_index][2], $tick->twse); //top
                        $data[$last_index][3] = (float) min($data[$last_index][3], $tick->twse); //low
                        $data[$last_index][4] = (float) $tick->twse; //close
                        // :TODO: volume 有值時要對應調整
                        $data[$last_index][5] = rand(100, 1000);
                        $pool = $row = array();
                    }
                }
                // 每週期的第一筆資料
                $row['time'] = date('Y/m/d H:i:s', $tick->time);
                $row['open'] = $tick->twse;
            }
            $pool[] = $tick->twse;

            // 最後一筆資料，且不是收盤最後的tick , 將pool 中的資料轉為candle, 此時 pool 一定大於1 
            if ( ($i == $length or $tick->time == $current_date_close_time) and count($pool) > 1 ) {
                $row['top'] = max($pool);
                $row['low'] = min($pool);
                $row['close'] = array_pop($pool);
                $row['volume'] = rand(10, 1000);
                $data[] = array($row['time'], (float) $row['open'], (float) $row['top'], (float) $row['low'], (float) $row['close'], (float) $row['volume']);
                $pool = $row = array();
            }
        }
        $this->json($data);
    }

    public function tickAction()
    {
        $debug = true;

        // :NOTE: 最後一根，如果是今日最後一根，要加上最後的一個pick
        //:TODO 若區間是"天", 要直接抓 Candle 的資料
        $periodicity = 1*60; //秒為單位

        $current = time();

        if ($debug) {
            $t = array();
            $current = time() - 30*60*60;
            $day_first = strtotime(date('Ymd', $current));
        }

        $period = $current - $day_first;
        $first_record_time = $day_first + (intval($period/$periodicity) * $periodicity);

        $t['day_first'] = date('Y/m/d H:i:s', $day_first);
        $t['first_record_time'] = date('Y/m/d H:i:s', $first_record_time);
        $t['time'] = date('Y/m/d H:i:s', $current);

        $ticks = Tick::search("`time` >= {$first_record_time} and `time` <= {$current}")->order('time ASC');
        $length = count($ticks);

        if ($length == 0) {
            $first_record_time = $day_first - $periodicity;
            $ticks = Tick::search("`time` >= {$first_record_time}")->order('time ASC');
            $length = count($ticks);
        }

        // 將tick 資料轉為 candle 
        $data = array();
        $pool = array();


        $i = 0;
        foreach ($ticks as $tick) {
            $i++;

            $t[] = array(date('d-M-y H:i:s', $tick->time), (float) $tick->twse, $i);

            if ($i == 1) {
                $data['time'] = date('Y/m/d H:i:s', $tick->time);
                $data['open'] = $tick->twse;
            }
            $pool[] = $tick->twse;

            if ($i == $length) {
                $data['top'] = max($pool);
                $data['low'] = min($pool);
                $data['close'] = array_pop($pool);
                $data['volume'] = rand(10, 1000);
            }
        }
        $data['t'] = $t;
        $this->json($data);
    }
}
