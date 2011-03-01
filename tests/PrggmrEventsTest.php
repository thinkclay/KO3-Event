<?php

use \prggmr\util as util;

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
        prggmr::listen('simpleevent', function($event, $a){
            return $a;
        });
        $this->assertEvent('simpleevent', array('test'), array(0 => 'test'));
    }

    public function testRegexEvent()
    {
        prggmr::listen('regex-([a-z]+)', function($event, $slug){
            return $slug;
        });
        $this->assertEvent('regex-test', array(), array(0 => 'test'));
    }

    public function testRegexEventWithParams()
    {
        prggmr::listen('regex2-([a-z]+)', function($event, $slug, $p, $p2){
            return $slug.$p.$p2;
        });
        $this->assertEvent('regex2-test', array('one', 'two'), array(0 => 'testonetwo'));
    }

    /**
     * @expectedException LogicException
     */
    public function testInvalidParamsException()
    {
        prggmr::listen('regex2-([a-z]+)', function($event, $slug, $p, $p2){
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
        prggmr::listen('mutiple-returns', function($event, $i){
            return strtoupper($i);
        });

        prggmr::listen('mutiple-returns', function($event, $i){
            return strtolower($i);
        });

        $this->assertEvent('mutiple-returns', array('ThIsIsATest'), array(0 => 'THISISATEST', 1 => 'thisisatest'));
    }

    public function testRegexMulitpleEvents()
    {
        prggmr::listen('multi-regex-([a-z]+)', function($event, $p){
            return $p;
        });

        prggmr::listen('multi-regex-([a-z]+)', function($event, $p){
            return $p . '2';
        });

        $this->assertEvent('multi-regex-test', array(), array(0=>'test',1=>'test2'));
    }

    public function testRegexMulitpleEventsMultipleParams()
    {
        prggmr::listen('multi-regex-params-([a-z]+)', function($event, $p, $p2){
            return $p.$p2;
        });

        prggmr::listen('multi-regex-params-([a-z]+)', function($event, $p){
            return $p . '2';
        });

        $this->assertEvent('multi-regex-params-test', array('simple'), array(0=>'testsimple',1=>'test2'));
    }

    public function testParamReference()
    {
        prggmr::listen('reference', function($event, &$i){
            $i->node = 'Test';
        });

        prggmr::listen('reference', function($event, &$i){
            $i->node = $i->node . 'ing';
            return $i->node;
        });

        $i = new stdClass();

        $this->assertEvent('reference', array(&$i), array(0=>'', 1=>'Testing'));
    }

    public function testStaticInvoke()
    {
        prggmr::listen('magic_invoke', function($event, $param) {
            return $param;
        });

        $event = prggmr::magic_invoke(array('test'));
        $this->assertEquals($event, array(0=>'test'));
    }

    public function testStaticInvokeReferences()
    {
        prggmr::listen('magic_ref_invoke', function($event, &$obj){
            $obj->test = 'Hello';
            return $obj->test;
        });

        prggmr::listen('magic_ref_invoke', function($event, &$obj){
            $obj->test .= ' Im Nick';
            return $obj->test;
        });
        $obj = new stdClass();
        $event = prggmr::magic_ref_invoke(array(&$obj));
        $this->assertEquals($event, array(0=>'Hello', 1=>'Hello Im Nick'));

    }

    public function testEventHault()
    {
        prggmr::listen('event_hault', function($event){
            return 'test1';
        });

        prggmr::listen('event_hault', function($event){
            return false;
        });

        prggmr::listen('event_hault', function($event){
           return 'I will not be reached';
        });

        $this->assertEvent('event_hault', array(), array(0=>'test1'));
    }

    public function testEventShift()
    {
        prggmr::listen('event_shift', function($event){
            return 'test1';
        });

        prggmr::listen('event_shift', function($event){
            return 'test2';
        }, array('shift' => true, 'name' => 'shift_2'));

        prggmr::listen('event_shift', function($event){
            return 'test3';
        }, array('shift' => true, 'name' => 'shift_3'));

        $this->assertEvent('event_shift', array(), array('test3','test2','test1'));
    }

    public function testEventObjectTrigger()
    {
        prggmr::listen('event_object_trigger', function($event) {
            return $event->getState();
        });
        $event = new \prggmr\Event();
        $event->setListener('event_object_trigger');
        $event->trigger();
        $this->assertEquals(array(0 => \prggmr\Event::STATE_ACTIVE), $event->getResults());
    }

    public function testEventNonStackableResult()
    {
        prggmr::listen('event_nonstackable', function($event){
            return 1;
        });
        prggmr::listen('event_nonstackable', function($event){
            return 2;
        });
        $event = new \prggmr\Event();
        $event->setListener('event_nonstackable');
        $event->setResultsStackable(false);
        $event->trigger();
        $this->assertEquals(2, $event->getResults());
    }

    public function testEventChaining()
    {
        prggmr::listen('event_chain', function($event){
           return 1;
        });
        prggmr::listen('event_chain_parent', function($event){
           return $event->getState();
        });
        $parent = new \prggmr\Event();
        $parent->setListener('event_chain_parent');
        $event  = new \prggmr\Event($parent);
        $event->setListener('event_chain');
        $event->trigger();
        $this->assertEquals(array(0 => 1, 1 => array(0 => \prggmr\Event::STATE_ACTIVE)), $event->getResults());
    }

    public function testEventChainingUnstackable()
    {
        prggmr::listen('event_chain_unstack', function($event){
           return 1;
        });
        prggmr::listen('event_chain_parent_unstack', function($event){
           return $event->getState();
        });
        $parent = new \prggmr\Event();
        $parent->setListener('event_chain_parent_unstack');
        $event  = new \prggmr\Event($parent);
        $event->setListener('event_chain_unstack');
        $event->setResultsStackable(false);
        $event->trigger();
        $this->assertEquals(1, $event->getResults());
        $this->assertEquals(array(0 => \prggmr\Event::STATE_ACTIVE), $parent->getResults());
    }

    public function testEventReturnObject()
    {
        prggmr::listen('event_return_object', function($event){
            return 0;
        });

        $event  = new \prggmr\Event();
        $event->setListener('event_return_object');
        $this->assertSame($event, $event->trigger(array(), array('object' => true)));
    }

    /**
     * @expectedException RuntimeException
     */
    public function testEventErrorState()
    {
        prggmr::listen('event_error_state', function($event){
            $event->setState(\prggmr\Event::STATE_ERROR);
        });

        $event  = new \prggmr\Event();
        $event->setListener('event_error_state');
        $event->trigger();
    }

    public function testEventErrorStateSuppression()
    {
        prggmr::listen('event_error_state_suppress', function($event){
            $event->setState(\prggmr\Event::STATE_ERROR);
            return 0;
        });

        $event  = new \prggmr\Event();
        $event->setListener('event_error_state_suppress');
        $event->setResultsStackable(false);
        $this->assertEquals(0, $event->trigger(array(), array('suppress' => true)));
    }
}