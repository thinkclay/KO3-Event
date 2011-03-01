<?php

include_once 'bootstrap.php';

use \prggmr\util as data;

class DataInstanceTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->instance = new data\DataInstance();
    }

    public function testSimpleSetGet()
    {
        $this->instance->set('simplevar', 'value');
        $this->assertEquals('value', $this->instance->get('simplevar'));
    }

    public function testArraySetGet()
    {
        $this->instance->set(array(
            'simplevar' => value
        ));
        $this->assertEquals('value', $this->instance->get('simplevar'));
    }

    public function testDelimitSetGet()
    {
        $this->instance->set('simple.var', 'value');
        $this->assertArrayHasKey('var', $this->instance->get('simple'));
        $this->assertEquals('value', $this->instance->get('simple.var'));
    }

    public function testTieredSetGet()
    {
        $this->instance->set('simple.var', array(
            'nested' => array(
                'value' => 'test'
            )
        ));
        $this->assertArrayHasKey('value', $this->instance->get('simple.var.nested'));
        $this->assertEquals('test', $this->instance->get('simple.var.nested.value'));
    }

    public function testComplexIncorrectGetSet()
    {
        $this->instance->set('simple', array(
            'nested.nest' => array(
                'value.array' => 'test'
            )
        ));
        $this->assertArrayNotHasKey('nest', $this->instance->get('simple.var.nested'));
    }

    public function testComplexGetSet()
    {
        $this->instance->set(array(
            'simple.var' => array(
                'nested' => array(
                    'val' => array(
                        'matrix' => 'value'
                    )
                )
            )
        ));
        $this->assertArrayHasKey('val', $this->instance->get('simple.var.nested'));
        $this->assertEquals('value', $this->instance->get('simple.var.nested.nest.val.matrix'));
    }

    public function testOverwriteDefault()
    {
        $this->instance->set('test', 1);
        $this->instance->set('test', 2);
        $this->assertEquals(2, $this->instance->get('test'));
    }

    public function testOverwriteNone()
    {
        $this->instance->set('test', 1);
        $this->instance->set('test', 2, false);
        $this->assertEquals(1, $this->instance->get('test'));
    }

    public function testOverwriteComplex()
    {
        $this->instance->set('test.val', '1');
        $this->instance->set('test.val.isarray', '2');
        $this->assertArrayHasKey('isarray', $this->instance->get('test.val'));
    }

    public function testNullKeySet()
    {
        $this->assertFalse($this->instance->set(null, 'none'));
    }

    public function testArrayTraversalMethods()
    {
        $data = new data\DataInstance();
        $data->set(array('foo' => 'FOO','bar' => 'BAR','lib' => 'LIB','tar' => 'TAR'));
        $this->assertEquals('FOO', $data->current());
        $this->assertEquals('BAR', $data->next());
        $this->assertTrue($data->valid());
        $this->assertEquals('LIB', $data->next());
        $this->assertEquals('TAR', $data->next());
        $this->assertEquals('FOO', $data->rewind());
        $this->assertEquals('TAR', $data->prev());
        $this->assertTrue($data->valid());
    }

    public function testArrayFilterMethod()
    {
        $data = new data\DataInstance();
        $data->set(array('foo' => 'FOO','bar' => 'BAR','lib' => 'LIB','tar' => 'TAR'));
        $filter = $data->filter(function($value){
            if (strpos($value, 'R') !== false) {
                return true;
            }
            return false;
        });
        $this->assertEquals(array('bar' => 'BAR','tar' => 'TAR'), $filter);
    }

    public function testArrayEachMethod()
    {
        $data = new data\DataInstance();
        $data->set(array('foo' => 'FOO','bar' => 'BAR','lib' => 'LIB','tar' => 'TAR'));
        $each = $data->each(function($value){
            return strtolower($value);
        });
        $this->assertEquals(array('foo' => 'foo','bar' => 'bar','lib' => 'lib','tar' => 'tar'), $each);
    }

    public function testIteratorLoops()
    {
        $data = new data\DataInstance();
        $array = array('foo' => 'FOO','bar' => 'BAR','lib' => 'LIB','tar' => 'TAR');
        $data->set($array);
        foreach ($data as $k => $v) {
            $this->assertEquals($array[$k], $v);
        }
    }

    public function testCountable()
    {
        $data = new data\DataInstance();
        $data->set(array('foo' => 'FOO','bar' => 'BAR','lib' => 'LIB','tar' => 'TAR'));
        $this->assertEquals(4, count($data));
    }
}