<?php
include(__DIR__ . '/../webdata/init.inc.php');
echo time(). PHP_EOL;
echo microtime(true). PHP_EOL;
echo intval(microtime(true)*1000) . PHP_EOL;
echo date('Y/m/d H:i:s', 1502429400 ) . PHP_EOL;
echo date('Y/m/d H:i:s', 1502429395 ) . PHP_EOL;
echo date('Y/m/d H:i:s', 1502413200 ) . PHP_EOL;
echo date('Y/m/d H:i:s', 1502380800 ) . PHP_EOL;
echo strtotime('2017-08-10'). PHP_EOL;
