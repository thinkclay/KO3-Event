<?php
require 'bootstrap.php';

ini_set('memory_limit', '100MB');

$results = array();
$num_events = 1250;
$num_listeners = 1;

\prggmr\Engine::debug(true);

benchmark('start', 'total_time');

benchmark('start', 'add_event');
for($i=0;$i!=$num_events;$i++) {
    for ($a=0;$a!=$num_listeners;$a++) {
        \prggmr\Engine::subscribe($i, function(){
            return true;
        });
    }
}
$results['add_events'] = benchmark('stop', 'add_event');

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
die();

$eventfired = 0;
benchmark('start', 'invoke_event');
for($i=0;$i!=$num_events;$i++) {
    $eventfired += $num_listeners;
    \prggmr\Engine::bubble($i);
}
$results['invoke_events'] = benchmark('stop', 'invoke_event');

benchmark('start', 'static_invoke_event');
for($i=0;$i!=$num_events;$i++) {
    $a = (string) $i;
    \prggmr\Engine::$a();
}
$results['static_invoke_events'] = benchmark('stop', 'static_invoke_event');

$results['total_execution'] = benchmark('stop', 'total_time');

echo "Event Fired : $eventfired\n\n";

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
