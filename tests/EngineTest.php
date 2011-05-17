<?php
/**
 *  Copyright 2010 Nickolas Whiting
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 *
 *
 * @author  Nickolas Whiting  <me@nwhiting.com>
 * @package  prggmr
 * @copyright  Copyright (c), 2010 Nickolas Whiting
 */



/**
 * \prggmr\Engine Unit Tests
 */

include_once 'bootstrap.php';

class EngineTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->engine = \prggmr\Engine::instance();
    }

    public function tearDown()
    {
        $this->engine->flush();
    }

    public function assertEvent($event, $params, $expected)
    {
        $event = $this->engine->fire($event, $params);
        $this->assertEquals($expected, $event->getData());
    }

    /**
     * Methods Covered
     * @Engine\Subscribe
     *     @with option name
     * @Engine\hasSubscriber
     */
    public function testSubscribe()
    {
        $this->engine->subscribe('subscriber', function($event){}, array('name' => 'testSubscribe'));
        $this->assertTrue($this->engine->count() == 1);
    }

    /**
     * Methods Covered
     * @Engine\Subscription
     *      @with regex event name
     *      @with object return false
     *      @with single parameter injection
     */
    public function testEventSingleParameter()
    {
        $this->engine->subscribe('subscribe-parameter-single', function($event, $param1){
            $event->setData($param1);
        }, 'testEventSingleParameter');
        $this->assertEvent('subscribe-parameter-single', array('helloworld'), array('helloworld'));
    }

    /**
     * Methods Covered
     * @Engine\Subscription
     *      @with regex event name
     *      @with object return false
     *      @with multiple parameter injection from regex
     */
    public function testEventWithMultipleParameter()
    {
        $this->engine->subscribe('multiparam', function($event, $param1, $param2){
            $event->setData($param1.$param2);
        }, array('name' => 'testEventWithMultipleParameter'));
        $this->assertEvent('multiparam', array('hello', 'world'), array('helloworld'));
    }

    /**
     * Methods Covered
     * @Engine\Subscription
     *      @with regex event name
     *      @with object return false
     *      @with parameter injection from regex
     */
    public function testEventSingleRegexParameter()
    {
        $signal = new \prggmr\RegexSignal('regexparam/([a-z]+)');
        $this->engine->subscribe($signal, function($event, $param){
            $event->setData($param);
        }, array('name' => 'testEventSingleRegexParameter'));
        $this->assertEvent('regexparam/helloworld', array(), array('helloworld'));
    }

    /**
     * Methods Covered
     * @Engine\Subscription
     *      @with regex event name
     *      @with object return false
     *      @with multiple parameter injection from regex
     */
    public function testEventWithMultipleRegexParameter()
    {
        $signal = new \prggmr\RegexSignal('multiregexparam/([a-z]+)/([a-z]+)');
        $this->engine->subscribe($signal, function($event, $param1, $param2){
            $event->setData($param1.$param2);
        }, array('name' => 'testEventWithMultipleRegexParameter'));
        $this->assertEvent('multiregexparam/hello/world', array(), array('helloworld'));
    }

    /**
     * Methods Covered
     * @Engine\Subscription
     *      @with regex event name
     *      @with object return false
     *      @with multiple parameter injection from regex
     *      @with parameters supplied
     */
    public function testEventWithMultipleRegexAndMultipleSuppliedParamters()
    {
        $signal = new \prggmr\RegexSignal('multiparam2/([a-z]+)/([a-z]+)');
        $this->engine->subscribe($signal, function($event, $param1, $param2, $regex1, $regex2){
            $event->setData($param1.$param2.$regex1.$regex2);
        }, array('name' => 'testEventWithMultipleRegexAndMultipleSuppliedParamters'));
        $this->assertEvent('multiparam2/wor/ld', array('hel','lo'), array('helloworld'));
    }

    /**
     * Methods Covered
     * @Engine\Subscription
     *      @with regex event name
     *      @with object return false
     *      @with simplified regex
     */
    public function testRegexEventWithSimpleRegex()
    {
        $this->engine->subscribe(new \prggmr\RegexSignal('simpleregex/:name'), function($event, $name){
            $event->setData($name);
        }, array('name' => 'testRegexEventWithSimpleRegex'));
        $this->assertEvent('simpleregex/helloworld', array(), array('helloworld'));
    }

    /**
     * Methods Covered
     * @Engine\Subscription
     *      @with regex event name
     *      @with object return false
     *      @with multiple simplified regex
     */
    public function testEventWithMultipleSimpleRegex()
    {
        $this->engine->subscribe(new \prggmr\RegexSignal('multisimpleregex/:name/:slug'), function($event, $name, $slug){
            $event->setData($name.$slug);
        }, array('name' => 'testEventWithMultipleSimpleRegex'));
        $this->assertEvent('multisimpleregex/hello/world', array(), array('helloworld'));
    }

    /**
     * Methods Covered
     * @Engine\Subscription
     *      @with regex event name
     *      @with object return false
     *      @with multiple simplified regex
     *      @with mutplie supplied parameters
     */
    public function testEventWithMultipleSimpleRegexAndSuppliedParameters()
    {
        $this->engine->subscribe(new \prggmr\RegexSignal('multisimpleregexparamsupplied/:name/:slug'), function($event, $param1, $param2, $name, $slug){
            $event->setData($name.$param1.$slug.$param2);
        }, array('name' => 'testEventWithMultipleSimpleRegexAndSuppliedParameters'));
        $this->assertEvent('multisimpleregexparamsupplied/hel/wor', array('lo','ld'), array('helloworld'));
    }

    /**
     * Methods Covered
     * @Engine\Subscription
     *      @with regex event name
     *      @with object return false
     *      @with regex param
     *      @with simplified regex
     */
    public function testEventWithSimpleRegexAndRegexParameters()
    {
        $this->engine->subscribe(new \prggmr\RegexSignal('simpleandregex/:name/([a-z]+)'), function($event, $param1, $param2){
            $event->setData($param1.$param2);
        }, array('name' => 'testEventWithSimpleRegexAndRegexParameters'));
        $this->assertEvent('simpleandregex/hello/world', array(), array('helloworld'));
    }

    /**
     * Methods Covered
     * @Engine\Subscription
     *      @with regex event name
     *      @with object return false
     *      @with regex param
     *      @with simplified regex
     */
    public function testEventWithSimpleRegexRegexAndSuppliedParameters()
    {
        $this->engine->subscribe(new \prggmr\RegexSignal('simpleregexsupplied/:name/([a-z]+)'), function($event, $param1, $param2, $param3){
            $event->setData($param2.$param1.$param3);
        }, array('name' => 'testEventWithSimpleRegexRegexAndSuppliedParameters'));
        $this->assertEvent('simpleregexsupplied/hel/ld', array('lowor'), array('helloworld'));
    }

    /**
     * Methods Covered
     * @Engine\version
     */
    public function testVersion()
    {
        $this->assertEquals($this->engine->version(), PRGGMR_VERSION);
    }

    /**
     * Methods Covered
     * @Engine\flush
     */
    public function testFlush()
    {
        $this->engine->subscribe('test', function(){});
        $this->assertTrue($this->engine->count() == 1);
        $this->engine->flush();
        $this->assertTrue($this->engine->count() == 0);
    }

    /**
     * @expectedException RuntimeException
     */
    public function testEngineErrorState()
    {
        $this->engine->subscribe('stateerrortest', function($event){
            $event->setState(\prggmr\Event::STATE_ERROR);
        }, 'exception_test');
        $event = $this->engine->fire('stateerrortest');
    }

    /**
     * Methods Covered
     * @Engine\bubble
     *      @with event halt
     */
    public function testEventHalt()
    {
        $this->engine->subscribe('halt', function($event){
            $event->setData('Hello');
        });
        // this halts it :)
        $this->engine->subscribe('halt', function(){
            return false;
        });
        $this->engine->subscribe('halt', function(){
            return 'World';
        });
        $this->assertEvent('halt', array(), array('Hello'));
    }
    
    /**
     * Test event chaining
     */
    public function testEventChain()
    {
        $this->engine->subscribe(array('test', 'chain_link_1'), function($event){
           $event->setData('one'); 
        });
        $this->engine->subscribe(array('chain_link_1', 'chain_link_2'), function($event){
            $event->setData('two');
        });
        $this->engine->subscribe('chain_link_2', function($event){
            $event->setData('three');
        });
        $event = $this->engine->fire('test');
        $this->assertEquals(array('one'), $event->getData());
        $this->assertInstanceOf('\prggmr\Event', $event->getChain());
        $this->assertInstanceOf('\prggmr\Event', $event->getChain()->getChain());
        $this->assertEquals(array('two'), $event->getChain()->getData());
        $this->assertEquals(array('three'), $event->getChain()->getChain()->getData());
    }
}