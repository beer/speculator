<?php
include(__DIR__ . '/../webdata/init.inc.php');
echo time(). PHP_EOL;
echo microtime(true). PHP_EOL;
echo intval(microtime(true)*1000) . PHP_EOL;
echo date('Y/m/d H:i:s', 946915200 ) . PHP_EOL;
echo date('Y/m/d H:i:s', 1462253400 ) . PHP_EOL;
echo date('Y/m/d H:i:s', 1462332265 ) . PHP_EOL;
echo strtotime('2017-08-10'). PHP_EOL;
exit;
$time = strtotime('2013-02-23');
$rows = Candle::search(array('time' => $time));
if (sizeof($rows)) {
    $rows->delete();
}
/*
$rows = FutureContract::search(array('date' => $time));
if (sizeof($rows)) {
    $rows->delete();
}
$rows = FutureTrade::search(array('date' => $time));
if (sizeof($rows)) {
    $rows->delete();
}
$rows = OptionContract::search(array('date' => $time));
if (sizeof($rows)) {
    $rows->delete();
}
$rows = OptionTrade::search(array('date' => $time));
if (sizeof($rows)) {
    $rows->delete();
}
 */
