<?php
require 'lib/prggmr.php';

\prggmr\Benchmark::benchmark('start', 'bench');

$engine = prggmr\Engine::instance();

for ($i=1;$i!=243;$i++) {
    for ($a=0;$a!=1;$a++) {
        $engine->subscribe('chain_'.$i, function($event){
            $event->setData('four');
        });
        //$engine->fire('chain_'.$i);
    }
}

var_dump(\prggmr\Benchmark::benchmark('stop', 'bench'));
