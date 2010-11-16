<?php

include_once 'bootstrap.php';

class PrggmrEventsTest extends \PHPUnit_Framework_TestCase
{
    public function assertEvent($event, $params, $expected)
    {
        $event = prggmr::trigger($event, $params);
        if (is_array($expected)) {
            $this->assertEquals($expected, $event);
        } else {
            $this->assertTrue($expected);
        }
    }
    
    public function testSimpleEvent()
    {
        prggmr::listen('simpleevent', function($a){
            return $a;
        });
        $this->assertEvent('simpleevent', array('test'), array(0 => 'test'));
    }
    
    public function testRegexEvent()
    {
        prggmr::listen('regex-([a-z]+)', function($slug){
            return $slug;
        });
        $this->assertEvent('regex-test', array(), array(0 => 'test'));
    }
    
    public function testRegexEventWithParams()
    {
        prggmr::listen('regex2-([a-z]+)', function($slug, $p, $p2){
            return $slug.$p.$p2;
        });
        $this->assertEvent('regex2-test', array('one', 'two'), array(0 => 'testonetwo'));
    }
    
    /**
     * @expectedException LogicException
     */
    public function testInvalidParamsException()
    {
        prggmr::listen('regex2-([a-z]+)', function($slug, $p, $p2){
            return $slug.$p.$p2;
        });
        $this->assertEvent('regex2-test', array('one'), null);
    }
    
    /**
     * @expectedException LogicException
     */
    public function testEventException()
    {
        prggmr::listen('simple-event-test', function(){
            throw new Exception('This should be caught!');
        });
        $this->assertEvent('simple-event-test', null, null);
    }
    
    public function testMulitpleEventReturns()
    {
        prggmr::listen('mutiple-returns', function($i){
            return strtoupper($i);
        });
        
        prggmr::listen('mutiple-returns', function($i){
            return strtolower($i);
        });
        
        $this->assertEvent('mutiple-returns', array('ThIsIsATest'), array(0 => 'THISISATEST', 1 => 'thisisatest'));
    }
    
    public function testRegexMulitpleEvents()
    {
        prggmr::listen('multi-regex-([a-z]+)', function($p){
            return $p;
        });
        
        prggmr::listen('multi-regex-([a-z]+)', function($p){
            return $p . '2';
        });
        
        $this->assertEvent('multi-regex-test', null, array(0=>'test',1=>'test2'));
    }
    
    public function testRegexMulitpleEventsMultipleParams()
    {
        prggmr::listen('multi-regex-params-([a-z]+)', function($p, $p2){
            return $p.$p2;
        });
        
        prggmr::listen('multi-regex-params-([a-z]+)', function($p){
            return $p . '2';
        });
        
        $this->assertEvent('multi-regex-params-test', array('simple'), array(0=>'testsimple',1=>'test2'));
    }
    
    public function testParamReference()
    {
        prggmr::listen('reference', function(&$i){
            $i->node = 'Test';
        });
        
        prggmr::listen('reference', function(&$i){
            $i->node = $i->node . 'ing';
            return $i->node;
        });
        
        $i = new stdClass();
        
        $this->assertEvent('reference', array(&$i), array(0=>'', 1=>'Testing'));
    }
    
    public function testMultiParamsReferenceRegexMutliEvents()
    {
        prggmr::listen('ref-param-regex-([a-z]+)', function($reg, &$obj, $str) {
            $obj->node = 'Hello';
            return $reg.$str;
        }, array('name' => 'Ref Test Event 1'));
        
        prggmr::listen('ref-param-regex-([a-z]+)', function($reg, &$obj) {
            $obj->node .= ' My Name';
            return $reg;
        }, array('name' => 'Ref Test Event 2'));
        
        prggmr::listen('ref-param-regex-([a-z]+)', function($reg, &$obj, $str, $module) {
            $obj->node .= ' is '.$module;
            return $obj->node;
        }, array('name' => 'Ref Test Event 3'));
        
        $obj = new stdClass();
        
        $this->assertEvent('ref-param-regex-test', array(&$obj, 'Test', 'Nick'), array(
            0 => 'testTest',1=>'test',2=>'Hello My Name is Nick'
        ));
    }
}