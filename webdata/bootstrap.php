<?php
include(__DIR__ . '/init.inc.php');

Pix_Session::setAdapter('cookie', array('secret' => 'SpeculatorDotIm'));
Pix_Controller::dispatch(__DIR__);

