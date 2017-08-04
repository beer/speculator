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
        $periodicity = 60; //秒為單位
        $start = strtotime('2017-08-02');
        $end = strtotime('2017-08-03');

        $ticks = Tick::search("`date` >= {$start} AND `date` <= {$end}")->order('time ASC');
        $length = count($ticks);

        $data = array();

        // 將tick 資料轉為 candle 
        $row = array();
        $pool = array();

        $check_date = '';
        // :DEBUG:
        //$t = array();
        
        $i = 0;
        foreach ($ticks as $tick) {
            $i++;
            // :DEBUG:
            //$t[] = array(date('d-M-y H:i:s', $tick->time), (float) $tick->twse, $i);

            if ($tick->time % $periodicity == 0) {
                // 第一筆資料且資料長度不只一筆
                if ($i == 1 and $length > 1) {
                    $row['time'] = date('Y/m/d H:i:s', $tick->time);
                    $row['open'] = $tick->twse;
                    $pool[] = $tick->twse;
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
                }
                // 每週期的第一筆資料
                $row['time'] = date('Y/m/d H:i:s', $tick->time);
                $row['open'] = $tick->twse;
            }
            $pool[] = $tick->twse;
            $check_date = $tick->date;

            // 最後一筆資料，將pool 中的資料轉為candle
            if ($i == $length or $check_date != $tick->date) {
                $row['top'] = max($pool);
                $row['low'] = min($pool);
                $row['close'] = array_pop($pool);
                $row['volume'] = rand(10, 1000);
                $data[] = array($row['time'], (float) $row['open'], (float) $row['top'], (float) $row['low'], (float) $row['close'], (float) $row['volume']);
                $pool = $row = array();
            }
        }
        // :DEBUG:
        //$data['ticks'] = $t;
        $this->json($data);
    }

    public function tickAction()
    {
    }
}
