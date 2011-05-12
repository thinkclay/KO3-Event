<?php
class test { function one(){} }

require 'lib/prggmr.php';
$engine = prggmr\Engine::instance();
$engine->subscribe('test_signal', array('test', 'one'));
var_dump($engine);