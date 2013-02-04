<?php
class AjaxController extends Pix_Controller
{
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

    public function getDayInfoAction()
    {
        $day = strtotime($_POST['day']);
        $v = $this->view;
        $v->day = $day;

        $content = $this->view->partial('ajax/dayinfo.phtml', $v);
        $data = array('error' => 0, 'content' => $content);
        $this->json($data);
    }
}
