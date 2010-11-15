<?php

include_once 'bootstrap.php';

class PrggmrArrayMatrixesTest extends \PHPUnit_Framework_TestCase
{
    public function testSimpleSetGet()
    {
        prggmr::set('simplevar', 'value');
        $this->assertEquals('value', prggmr::get('simplevar'));
    }
    
    public function testArraySetGet()
    {
        prggmr::set(array(
            'simplevar' => value
        ));
        $this->assertEquals('value', prggmr::get('simplevar'));
    }
    
    public function testDelimitSetGet()
    {
        prggmr::set('simple.var', 'value');
        $this->assertArrayHasKey('var', prggmr::get('simple'));
        $this->assertEquals('value', prggmr::get('simple.var'));
    }
    
    public function testTieredSetGet()
    {
        prggmr::set('simple.var', array(
            'nested' => array(
                'value' => 'test'
            )
        ));
        $this->assertArrayHasKey('value', prggmr::get('simple.var.nested'));
        $this->assertEquals('test', prggmr::get('simple.var.nested.value'));
    }
    
    public function testComplexIncorrectGetSet()
    {
        prggmr::set('simple', array(
            'nested.nest' => array(
                'value.array' => 'test'
            )
        ));
        $this->assertArrayNotHasKey('nest', prggmr::get('simple.var.nested'));
    }
    
    public function testComplexGetSet()
    {
        prggmr::set(array(
            'simple.var' => array(
                'nested' => array(
                    'val' => array(
                        'matrix' => 'value'
                    )
                )
            )
        ));
        $this->assertArrayHasKey('val', prggmr::get('simple.var.nested'));
        $this->assertEquals('value', prggmr::get('simple.var.nested.nest.val.matrix'));
    }
    
    public function testOverwriteDefault()
    {
        prggmr::set('test', 1);
        prggmr::set('test', 2);
        $this->assertEquals(2, prggmr::get('test'));
    }
    
    public function testOverwriteNone()
    {
        prggmr::set('test', 1);
        prggmr::set('test', 2, false);
        $this->assertEquals(1, prggmr::get('test'));
    }
    
    public function testOverwriteComplex()
    {
        prggmr::set('test.val', '1');
        prggmr::set('test.val.isarray', '2');
        $this->assertArrayHasKey('isarray', prggmr::get('test.val'));
    }
}