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

        $future_1 = FutureTrade::search(array('date' => $day, 'user_id' => 1))->first();
        $future_2 = FutureTrade::search(array('date' => $day, 'user_id' => 2))->first();
        $future_3 = FutureTrade::search(array('date' => $day, 'user_id' => 3))->first();
        $content =<<< EOT
自營商:{$future_1->diff}
投信:{$future_2->diff}
外資:{$future_3->diff}
EOT;
        $data = array('error' => 0, 'content' => $content);
        $this->json($data);
    }
}
