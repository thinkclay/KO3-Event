<?php

include_once 'bootstrap.php';

use \prggmr\util as data;

class DataStaticTest extends \PHPUnit_Framework_TestCase
{
    public function testSimpleSetGet()
    {
        data\DataStatic::set('simplevar', 'value');
        $this->assertEquals('value', data\DataStatic::get('simplevar'));
    }

    public function testArraySetGet()
    {
        data\DataStatic::set(array(
            'simplevar' => value
        ));
        $this->assertEquals('value', data\DataStatic::get('simplevar'));
    }

    public function testDelimitSetGet()
    {
        data\DataStatic::set('simple.var', 'value');
        $this->assertArrayHasKey('var', data\DataStatic::get('simple'));
        $this->assertEquals('value', data\DataStatic::get('simple.var'));
    }

    public function testTieredSetGet()
    {
        data\DataStatic::set('simple.var', array(
            'nested' => array(
                'value' => 'test'
            )
        ));
        $this->assertArrayHasKey('value', data\DataStatic::get('simple.var.nested'));
        $this->assertEquals('test', data\DataStatic::get('simple.var.nested.value'));
    }

    public function testComplexIncorrectGetSet()
    {
        data\DataStatic::set('simple', array(
            'nested.nest' => array(
                'value.array' => 'test'
            )
        ));
        $this->assertArrayNotHasKey('nest', data\DataStatic::get('simple.var.nested'));
    }

    public function testComplexGetSet()
    {
        data\DataStatic::set(array(
            'simple.var' => array(
                'nested' => array(
                    'val' => array(
                        'matrix' => 'value'
                    )
                )
            )
        ));
        $this->assertArrayHasKey('val', data\DataStatic::get('simple.var.nested'));
        $this->assertEquals('value', data\DataStatic::get('simple.var.nested.nest.val.matrix'));
    }

    public function testOverwriteDefault()
    {
        data\DataStatic::set('test', 1);
        data\DataStatic::set('test', 2);
        $this->assertEquals(2, data\DataStatic::get('test'));
    }

    public function testOverwriteNone()
    {
        data\DataStatic::set('test', 1);
        data\DataStatic::set('test', 2, false);
        $this->assertEquals(1, data\DataStatic::get('test'));
    }

    public function testOverwriteComplex()
    {
        data\DataStatic::set('test.val', '1');
        data\DataStatic::set('test.val.isarray', '2');
        $this->assertArrayHasKey('isarray', data\DataStatic::get('test.val'));
    }

    public function testNullKeySet()
    {
        $this->assertFalse(data\DataStatic::set(null, 'none'));
    }
}