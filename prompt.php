#!/usr/bin/env php
<?php
for ($i = 1; isset($_SERVER['argv'][$i]); $i ++) {
    if (file_exists($_SERVER['argv'][$i])) {
        include($_SERVER['argv'][$i]);
    }
}
include(__DIR__ . '/webdata/init.inc.php');
include(__DIR__ . '/webdata/extsrc/pix/Pix/Prompt.php');
Pix_Prompt::init();
