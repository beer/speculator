<?php
include(__DIR__ . '/../webdata/init.inc.php');


echo time(). PHP_EOL;
echo microtime(true). PHP_EOL;
echo intval(microtime(true)*1000) . PHP_EOL;
echo date('Y/m/d H:i:s', 1502672695 ) . PHP_EOL;
echo date('Y/m/d H:i:s', 1502672400 ) . PHP_EOL;
echo date('Y/m/d H:i:s', 1502672405 ) . PHP_EOL;
echo date('Y/m/d H:i:s', 1502672703 ) . PHP_EOL;
echo strtotime('2017-08-25'). PHP_EOL;
echo strtotime('2017-08-24'). PHP_EOL;

/*
$res = Tick::search(1)->order('time ASC, id')->volumemode(1000);
foreach ($res as $r) {
    //echo $r->id . PHP_EOL;
}
 */

