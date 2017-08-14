<?php
include(__DIR__ . '/../webdata/init.inc.php');
echo time(). PHP_EOL;
echo microtime(true). PHP_EOL;
echo intval(microtime(true)*1000) . PHP_EOL;
echo date('Y/m/d H:i:s', 1502640000 ) . PHP_EOL;
echo date('Y/m/d H:i:s', 1502670895 ) . PHP_EOL;
echo date('Y/m/d H:i:s', 1502670900 ) . PHP_EOL;
echo date('Y/m/d H:i:s', 1502670895 ) . PHP_EOL;
echo strtotime('2017-08-14 08:45:00'). PHP_EOL;

