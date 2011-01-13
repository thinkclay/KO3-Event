<?php

include_once 'bootstrap.php';


class TestClass {

    public function test()
    {
       return get_class_name(get_class());
    }

}

class GetClassNameTest extends \PHPUnit_Framework_TestCase
{
    public function testGetClassName()
    {
        $view = new prggmr\render\View();
        //$cli  = new prggmr\cli\event\Handler($_SERVER['argv']);
        $std  = new \stdClass();
        $this->assertEquals('View', get_class_name($view));
        //$this->assertEquals('Handler', get_class_name($cli));
        $this->assertEquals('stdClass', get_class_name($std));
        $this->assertEquals('Test', get_class_name('Test'));
        $this->assertFalse(get_class_name(null));
        $this->assertFalse(get_class_name(array()));
        $class = new TestClass();
        $this->assertEquals('TestClass', $class->test());
    }
}