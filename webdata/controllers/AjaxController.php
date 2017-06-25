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
        $candles = Candle::search('`time` > ' . strtotime('2016-10-01'))->order('time ASC');
        $data = array();
        foreach ($candles as $c) {
            // UTC+8
            // volume 單位用(億)表示
            $data[] = array(date('d-M-y', $c->time), (float) $c->open, (float) $c->top, (float) $c->low, (float) $c->close, (float) $c->volume / 100000000);
        }
        $this->json($data);
    }
}
