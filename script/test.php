<?php
include(__DIR__ . '/../webdata/init.inc.php');
echo time(). PHP_EOL;
echo microtime(true). PHP_EOL;
echo intval(microtime(true)*1000) . PHP_EOL;
echo date('Y/m/d H:i:s', 1502672695 ) . PHP_EOL;
echo date('Y/m/d H:i:s', 1502672400 ) . PHP_EOL;
echo date('Y/m/d H:i:s', 1502672405 ) . PHP_EOL;
echo date('Y/m/d H:i:s', 1502672703 ) . PHP_EOL;
echo strtotime('2017-08-15'). PHP_EOL;

