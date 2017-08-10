<?php
class ChartController extends Pix_Controller
{
    public function indexAction()
    {

    }

    public function highstockAction()
    {

    }

    public function moietyAction()
    {

    }

    public function feedAction()
    {

    }

    public function realtimeAction()
    {

    }

    public function testAction()
    {

    }

    public function twiiAction()
    {
        $candles = Candle::search(1);
        $data = array();
        foreach ($candles as $c) {
            // UTC+8
            $data[] = array($c->time * 1000 + 28800*1000, (float) $c->open, (float) $c->top, (float) $c->low, (float) $c->close);
        }
        $this->json($data);
    }

    public function infoAction()
    {
        $v = $this->view;
        $v->day = strtotime($_GET['day']);
    }
}
