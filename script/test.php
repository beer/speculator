<?php
include(__DIR__ . '/../webdata/init.inc.php');
echo date('Y/m/d', 915552000);
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
