<?php


include_once 'bootstrap.php';

class ArrayUnshiftKeysTest extends \PHPUnit_Framework_TestCase
{
    public function testArrayUnshiftKeys()
    {
        $array = array('key1' => 'one');
        $this->assertEquals(array('key2'=>'two','key1'=>'one'), array_unshift_key('key2', 'two', $array));
        $array = array('key1' => 'one');
        $shift = array('key3' => 'three');
        array_unshift_key('key2', array_unshift_key('key4', 'four', $shift), $array);
        $this->assertEquals(array('key2' => array('key4' => 'four', 'key3'=>'three'), 'key1' => 'one'), $array);
        $array = array('1'=>'1','2'=>'2','3'=>'3','4'=>'4');
        array_unshift_key('2', '2', $array);
        $this->assertEquals(array('2'=>'2','1'=>'1','3'=>'3','4'=>'4'), $array);
        array_unshift_key(4, '4', $array);
        $this->assertEquals(array('4'=>'4','2'=>'2','1'=>'1','3'=>'3'), $array);
    }
}