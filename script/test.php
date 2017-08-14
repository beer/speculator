<?php
include(__DIR__ . '/../webdata/init.inc.php');
/*
echo time(). PHP_EOL;
echo microtime(true). PHP_EOL;
echo intval(microtime(true)*1000) . PHP_EOL;
echo date('Y/m/d H:i:s', 1502672695 ) . PHP_EOL;
echo date('Y/m/d H:i:s', 1502672400 ) . PHP_EOL;
echo date('Y/m/d H:i:s', 1502672405 ) . PHP_EOL;
echo date('Y/m/d H:i:s', 1502672703 ) . PHP_EOL;
echo strtotime('2017-08-14 08:45:00'). PHP_EOL;
 */

$ex_id = 2510000;
for ($i = 2510090 ; $i < 4000000 ; $i += 10000) {
    $vs = TickVolume::search("`id` > {$ex_id} and `id` <= {$i}")->order('time ASC');

    foreach ($vs as $v) {
        $tick = Tick::search("`time` = {$v->time}");
        if (count($tick)) {
            $tick = $tick->first();
            if ($tick->volume != intval($v->volume/1000000)) {
                $tick->volume = intval($v->volume/1000000);
                $tick->save();
                echo 'TickVolume:' . $v->id . PHP_EOL;
            }
        }
    }
    $ex_id = $i;
}
