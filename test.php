<?php
class test {
    function one($event) {
        $event->setResults('hahaha');
    }
}

require 'lib/prggmr.php';
$engine = prggmr\Engine::instance();

$signal = new prggmr\Signal('chained', 'test_signal');
$engine->subscribe($signal, function($event){
    $event->setResults('WOAH');
});

$engine->subscribe('test_signal', array('test', 'one'));
$engine->subscribe('test_signal', function($event){
    $event->setResults('DAMN A CHAIN!');
    $event->halt();
}, 1);


var_dump($engine->bubble('chained'));