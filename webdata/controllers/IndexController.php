<?php
class IndexController extends Pix_Controller
{
    public function indexAction()
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

    public function dayinfoAction()
    {
        $v = $this->view;
        $v->day = strtotime($_GET['day']);
    }
}
