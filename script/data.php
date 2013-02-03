<?php
include(__DIR__ . '/../webdata/init.inc.php');

echo date('Y/m/d', 1357603200000 / 1000) . PHP_EOL;
echo date('Y/m/d', 1357574400000 / 1000) . PHP_EOL;
echo date('Y/m/d', 1360540800000 / 1000) . PHP_EOL;
echo date('Y/m/d', 1360425600000 / 1000) . PHP_EOL;
exit;
$cs = Candle::search(1);
foreach ($cs as $c) {
    echo date('Y/m/d', $c->time) . ', js:' . $c->time * 1000 . ",{$c->open}, {$c->top}, {$c->low}, {$c->close}\n";
}
