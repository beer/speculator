<?php
include(__DIR__ . '/../webdata/init.inc.php');
$day = strtotime('2017-08-14');
$lost_tick = $lost_volume = 0;
$lost_ticks = $lost_volumes = array();

$ticks = Tick::search(array('date' => $day))->order('`time` ASC');
echo 'Ticks:' . count($ticks) . PHP_EOL;
$ex_time = strtotime('2017-08-14 09:00:00');
foreach ($ticks as $t) {
    if (($t->time - $ex_time) != 5 and ($t->time - $ex_time) != 0) {
        //echo date('Y/m/d H:i:s', $ex_time) . ' ~ ' . date('Y/m/d H:i:s', $t->time) . ' 間: ' . (($t->time - $ex_time) / 5 - 1) . ' 筆資料' . PHP_EOL;
        $lost_ticks[] = date('Y/m/d H:i:s', $ex_time) . ' ~ ' . date('Y/m/d H:i:s', $t->time) . ' 間: ' . (($t->time - $ex_time) / 5 - 1) . ' 筆資料';
        $lost_tick += (($t->time - $ex_time) / 5 - 1);
    }
    $ex_time = $t->time;
}
echo '共 ' . $lost_ticks . '筆資料' . PHP_EOL;

$volumes = TickVolume::search(array('date' => $day))->order('`time` ASC');
echo 'Volumes:' . count($volumes) . PHP_EOL;
$ex_time = strtotime('2017-08-14 09:00:00');
foreach ($volumes as $t) {
    if (($t->time - $ex_time) != 5 and ($t->time - $ex_time) != 0) {
        //echo date('Y/m/d H:i:s', $ex_time) . ' ~ ' . date('Y/m/d H:i:s', $t->time) . ' 間: ' . (($t->time - $ex_time) / 5 - 1) . ' 筆資料' . PHP_EOL;
        $lost_volumes[] = date('Y/m/d H:i:s', $ex_time) . ' ~ ' . date('Y/m/d H:i:s', $t->time) . ' 間: ' . (($t->time - $ex_time) / 5 - 1) . ' 筆資料';
        $lost_volume += (($t->time - $ex_time) / 5 - 1);
    }
    $ex_time = $t->time;
}

echo '共 ' . count($ticks) . "(少$lost_tick)" . ' ticks 筆資料' . ', ' . count($volumes) . "(少$lost_volume)" . ' volumes 筆資料沒抓' . PHP_EOL;
for ($i = 0 ; $i < count($lost_ticks) ; $i++) {
    echo $lost_ticks[$i] . '/' . $lost_volumes[$i] . PHP_EOL;
    sleep(1);
}
echo '共 ' . count($ticks) . "(少$lost_tick)" . ' ticks 筆資料' . ', ' . count($volumes) . "(少$lost_volume)" . ' volumes 筆資料沒抓' . PHP_EOL;
