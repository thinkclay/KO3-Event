<?php
require 'bootstrap.php';
/**
 * Simple benchmark test which calls prggmr to listen
 * to events 1 - 100,000 and calls 3 listeners for each event
 * which will call 300,000 events.
 */

$results = array();
$num_events = 100000;

prggmr::analyze('bench_begin', array('name' => 'add_event'));
for($i=0;$i!=$num_events;$i++) {
    prggmr::listen($i, function(){
        return $i;
    });
}
$results['add_events'] = prggmr::analyze('bench_stop', array('name' => 'add_event'));

prggmr::analyze('bench_begin', array('name' => 'invoke_event'));
for($i=0;$i!=$num_events;$i++) {
    prggmr::trigger($i);
}
$results['invoke_events'] = prggmr::analyze('bench_stop', array('name' => 'invoke_event'));

prggmr::analyze('bench_begin', array('name' => 'static_invoke_event'));
for($i=0;$i!=$num_events;$i++) {
    $a = (string) $i;
    prggmr::$a();
}
$results['static_invoke_events'] = prggmr::analyze('bench_stop', array('name' => 'static_invoke_event'));

foreach ($results as $k => $v) {
    echo "\n\n";
    echo "Benchmark ($k)\n";
    foreach ($v as $a => $b) {
        if (is_array($b)) {} else {
            echo "$a : $b\n";
        }
    }
    echo "\n\n";
}