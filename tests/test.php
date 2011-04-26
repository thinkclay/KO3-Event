<?php
require 'bootstrap.php';

$one = new \prggmr\Queue('one');
$one->push(new \prggmr\Subscription('my_event'));

$spl = new SplObjectStorage();
$spl->attach($one);

$test = new \prggmr\Queue('one');
//$test->attach(new \prggmr\Subscription('my_event'));
var_dump($spl->contains($one));

//$stack = new SplDoublyLinkedList();
//
//$stack[] = 'one';
//$stack['a'] = 'one';
//$stack->offsetSet(0, 'test');
////$stack->offsetSet(0, 'one');
//
//var_dump($stack);
