<?php
require 'bootstrap.php';

echo 0x03."\n";

\prggmr\Engine::debug(true);

$index = array();

//for ($i=0;$i!=1000;$i++){
//    $index[] = str_random(6);
//}
//$start = memory_get_usage();
//$array = array();
//for ($i=0;$i!=1000;$i++){
//    $array[$index[$i]] = true;
//    $asd = $array[$index[$i]];
//}

//for ($i=0;$i!=1000;$i++){
//    $index[] = rand(1000, 9999);
//}
//$array = array();
//$start = memory_get_usage();
//for ($i=0;$i!=1000;$i++){
//    $array[$index[$i]] = true;
//    $asd = $array[$index[$i]];
//}

$start = memory_get_usage();
$prg = new \prggmr\Queue();
$end = memory_get_usage();
echo \prggmr\Benchmark::formatBytes($end - $start, 5)."\n";

$sqlObject = new SplQueue();

$start = memory_get_usage();
//$prg = new SplQueue();
//$end = memory_get_usage();
for ($i=0;$i!=1;$i++){
    $sqlObject->push(new \prggmr\Event());
    //$asd = $array[$index[$i]];
}

$end = memory_get_peak_usage();

echo \prggmr\Benchmark::formatBytes($end - $start, 5)."\n";


//$index = array();
//for ($i=0;$i!=100000;$i++){
//    $index[] = rand(100000, 999999);
//}
//$array = array();
//\prggmr\Benchmark::benchmark('start', 'numberical');
//for ($i=0;$i!=100000;$i++){
//    $array[$index[$i]] = true;
//    $asd = $array[$index[$i]];
//}
//$results['numberical indicies'] = \prggmr\Benchmark::benchmark('stop', 'numberical');
//
//foreach ($results as $k => $v) {
//    echo "\n\n";
//    echo "Benchmark ($k)\n";
//    foreach ($v as $a => $b) {
//        if (is_array($b)) {} else {
//            echo "$a : $b\n";
//        }
//    }
//    echo "\n\n";
//}