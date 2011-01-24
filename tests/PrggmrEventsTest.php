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
        $this->assertEvent('simple-event-test', array(), null);
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

        $this->assertEvent('multi-regex-test', array(), array(0=>'test',1=>'test2'));
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

    public function testStaticInvoke()
    {
        prggmr::listen('magic_invoke', function($param) {
            return $param;
        });

        $event = prggmr::magic_invoke(array('test'));
        $this->assertEquals($event, array(0=>'test'));
    }

    public function testStaticInvokeReferences()
    {
        prggmr::listen('magic_ref_invoke', function(&$obj){
            $obj->test = 'Hello';
            return $obj->test;
        });

        prggmr::listen('magic_ref_invoke', function(&$obj){
            $obj->test .= ' Im Nick';
            return $obj->test;
        });
        $obj = new stdClass();
        $event = prggmr::magic_ref_invoke(array(&$obj));
        $this->assertEquals($event, array(0=>'Hello', 1=>'Hello Im Nick'));

    }

    public function testEventHault()
    {
        prggmr::listen('event_hault', function(){
            return 'test1';
        });

        prggmr::listen('event_hault', function(){
            return false;
        });

        prggmr::listen('event_hault', function(){
           return 'I will not be reached';
        });

        $this->assertEvent('event_hault', array(), array(0=>'test1',1=>false));
    }

    public function testEventShift()
    {
        prggmr::listen('event_shift', function(){
            return 'test1';
        });

        prggmr::listen('event_shift', function(){
            return 'test2';
        }, array('shift' => true, 'name' => 'shift_2'));

        prggmr::listen('event_shift', function(){
            return 'test3';
        }, array('shift' => true, 'name' => 'shift_3'));

        $this->assertEvent('event_shift', array(), array('test3','test2','test1'));
    }
}