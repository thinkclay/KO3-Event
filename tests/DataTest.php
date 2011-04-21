<?php

include_once 'bootstrap.php';

class DataTest extends \PHPUnit_Framework_TestCase
{
    public function testSimpleSetGet()
    {
        \prggmr\Data::set('simplevar', 'value');
        $this->assertEquals('value', \prggmr\Data::get('simplevar'));
    }

    public function testArraySetGet()
    {
        \prggmr\Data::set(array(
            'simplevar' => value
        ));
        $this->assertEquals('value', \prggmr\Data::get('simplevar'));
    }

    public function testDelimitSetGet()
    {
        \prggmr\Data::set('simple.var', 'value');
        $this->assertArrayHasKey('var', \prggmr\Data::get('simple'));
        $this->assertEquals('value', \prggmr\Data::get('simple.var'));
    }

    public function testTieredSetGet()
    {
        \prggmr\Data::set('simple.var', array(
            'nested' => array(
                'value' => 'test'
            )
        ));
        $this->assertArrayHasKey('value', \prggmr\Data::get('simple.var.nested'));
        $this->assertEquals('test', \prggmr\Data::get('simple.var.nested.value'));
    }

    public function testComplexIncorrectGetSet()
    {
        \prggmr\Data::set('simple', array(
            'nested.nest' => array(
                'value.array' => 'test'
            )
        ));
        $this->assertArrayNotHasKey('nest', \prggmr\Data::get('simple.var.nested'));
    }

    public function testComplexGetSet()
    {
        \prggmr\Data::set(array(
            'simple.var' => array(
                'nested' => array(
                    'val' => array(
                        'matrix' => 'value'
                    )
                )
            )
        ));
        $this->assertArrayHasKey('val', \prggmr\Data::get('simple.var.nested'));
        $this->assertEquals('value', \prggmr\Data::get('simple.var.nested.nest.val.matrix'));
    }

    public function testOverwriteDefault()
    {
        \prggmr\Data::set('test', 1);
        \prggmr\Data::set('test', 2);
        $this->assertEquals(2, \prggmr\Data::get('test'));
    }

    public function testOverwriteNone()
    {
        \prggmr\Data::set('test', 1);
        \prggmr\Data::set('test', 2, false);
        $this->assertEquals(1, \prggmr\Data::get('test'));
    }

    public function testOverwriteComplex()
    {
        \prggmr\Data::set('test.val', '1');
        \prggmr\Data::set('test.val.isarray', '2');
        $this->assertArrayHasKey('isarray', \prggmr\Data::get('test.val'));
    }

    public function testNullKeySet()
    {
        $this->assertFalse(\prggmr\Data::set(null, 'none'));
    }
}