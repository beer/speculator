<?php
//1501821000694
//1501822007652
//1501822158238
//1501822258121
echo date('Y/m/d H:i:s', 1501822258);
echo PHP_EOL;
exit;

$fp = file('twse_20170626.csv', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
echo count($fp);
$pattern = '/^(?P<year>\d+)年(?P<month>\d+)月(?P<day>\d+)日每(?P<frequency>(一分鐘|五分鐘|5|10|15))(秒)?指數*/';
$title = '93年10月21日每一分鐘指數統計';
preg_match($pattern, $title, $matches);
var_dump($matches);


echo date('Y/m/d', 915465600);
echo PHP_EOL;
echo date('Y/m/d H:i:s', 1391961600);
echo PHP_EOL;
echo date('Y/m/d H:i:s', 1495468800);
echo PHP_EOL;
