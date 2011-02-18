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
        $std  = new \stdClass();
        $this->assertEquals('stdClass', get_class_name($std));
        $this->assertEquals('Test', get_class_name('Test'));
        $this->assertFalse(get_class_name(null));
        $this->assertFalse(get_class_name(array()));
        $class = new TestClass();
        $this->assertEquals('TestClass', $class->test());
    }
}