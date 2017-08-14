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

    public function realtimeAction()
    {
        $candles = Candle::search(1)->order('time DESC')->offset(130);
        $first = $candles->first();
        $candles = Candle::search('`time` > ' . $first->time)->order('time ASC');
        //$candles = Candle::search('`time` >= ' . strtotime('2017/02/06') . ' AND `time` < ' . strtotime('2017/08/11'))->order('time ASC');
        $data = array();
        foreach ($candles as $c) {
            $data[] = array(date('Y/m/d', $c->time), (float) $c->open, (float) $c->top, (float) $c->low, (float) $c->close, (float) $c->volume / 100000000);
        }
        $this->json($data);
    }

    public function feedAction()
    {
        $periodicity = 1*60; //秒為單位
        $start = strtotime('2017-08-03');
        $end = strtotime('2017-08-04 09:35');

        $open_time = '09:00'; //每日開盤時間
        $close_time = '13:30'; //每日收盤時間

        $ticks = Tick::search("`time` >= {$start} AND `time` <= {$end}")->order('time ASC');
        $length = count($ticks);

        $data = array();

        // 將tick 資料轉為 candle 
        $row = array();
        $pool = array();

        /*
        // 若是日K, 直接回傳 Candle 資料 
        //:TODO 若區間是"天", 要直接抓 Candle 的資料
        if ($periodicity == 86404) {
            $candle = Candle::search("`date` == " . strtotime('Ymd', $check_date_time));

            if (count($candle)) {
                $candle = $candle->first();
            } else { // 預防Cron 抓 Candle 時間的 Delay, 每天3:00pm 才跑
                $check_date_time = $check_date_time - 86400;
                $candle = Candle::search("`date` == " . strtotime('Ymd', $check_date_time));
                $candle = $candle->first();
            }
            $data['time'] = date('Y/m/d H:i:s', $tick->time);
            $data['open'] = $tick->twse;
            $data['top'] = max($pool);
            $data['low'] = min($pool);
            $data['close'] = array_pop($pool);
            $data['volume'] = rand(10, 1000);
            $this->json($data);
        } */

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
                        //echo $current_date_close_time . PHP_EOL;
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
        $open = '09:00';
        $close = '13:30';

        // :NOTE: 最後一根，如果是今日最後一根，要加上最後的一個tick
        $periodicity = 24*60*60; //秒為單位

        $now = time();

        if ($debug) {
            $t = array();
            //$now = $now - 12*60*60;
        }
        $open_time = strtotime(date('Ymd', $now) . " {$open}");
        $close_time = strtotime(date('Ymd', $now) . " {$close}");
        $week = date('N', $now);

        $last_tick = Tick::search(1)->order('time DESC')->first();
        //var_dump($last_tick->toArray());
        //exit;
        $last_tick_date_time = $last_tick->date;

        // 時間大於最後一筆資料時間，取最後一筆的candle
        if ($week > 6 or $now > $close_time or $now < $open_time) {
            $check_date_time = $last_tick_date_time;
            $tick_start_time = $last_tick->time - $periodicity;
            if ($periodicity == 86400) {
                $tick_start_time = strtotime(date('Ymd', $last_tick->time) . " {$open}");
            }
        } else { // 當
            $check_date_time = $now;
            $period = $now - $open_time;
            $tick_start_time = $open_time + (intval($period/$periodicity) * $periodicity);
        }

        $t['now'] = date('Y/m/d H:i:s', $now);
        $t['open_time'] = date('Y/m/d H:i:s', $open_time);
        $t['close_time'] = date('Y/m/d H:i:s', $close_time);
        $t['check_date'] = date('Y/m/d H:i:s', $check_date_time);
        $t['tick_start_time'] = date('Y/m/d H:i:s', $tick_start_time);

        //$this->json($t);

        $ticks = Tick::search("`time` >= {$tick_start_time} and `time` <= {$now}")->order('time ASC');

        $length = count($ticks);
        //echo $length;

        // 將tick 資料轉為 candle 
        $data = array();
        $pool = array();


        $i = 0;
        foreach ($ticks as $tick) {
            $i++;

            //$t[] = array(date('d-M-y H:i:s', $tick->time), (float) $tick->twse, $i);

            if ($i == 1) {
                $data['time'] = date('Y/m/d H:i:s', $tick->time);
                $data['open'] = (float) $tick->twse;
            }
            $pool[] = $tick->twse;

            if ($i == $length) {
                $data['top'] = (float) max($pool);
                $data['low'] = (float) min($pool);
                $data['close'] = (float) array_pop($pool);
                $data['volume'] = (float) rand(10, 1000);
            }
        }
        
        //array_unshift($data, $t);

        $this->json($data);
    }
}
