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
    public function tearDown()
    {
        \prggmr\Engine::flush();
    }

    public function assertEvent($event, $params, $expected, $options = array())
    {
        $defaults = array('stackResults' => false);
        $options += $defaults;
        $event = \prggmr\Engine::bubble($event, $params, $options);
        $this->assertEquals($expected, $event);
    }

    /**
     * Methods Covered
     * @Engine\Subscribe
     *     @with option name
     * @Engine\hasSubscriber
     */
    public function testSubscribe()
    {
        \prggmr\Engine::subscribe('subscriber', function($event){}, array('name' => 'testSubscribe'));
        $this->assertTrue(\prggmr\Engine::hasSubscriber('testSubscribe', 'subscriber'));
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
        \prggmr\Engine::subscribe('subscribe-parameter-single', function($event, $param1){
            return $param1;
        }, array('name' => 'testEventSingleParameter'));
        $this->assertEvent('subscribe-parameter-single', array('helloworld'), 'helloworld');
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
        \prggmr\Engine::subscribe('multiparam', function($event, $param1, $param2){
            return $param1.$param2;
        }, array('name' => 'testEventWithMultipleParameter'));
        $this->assertEvent('multiparam', array('hello', 'world'), 'helloworld');
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
        \prggmr\Engine::subscribe('regexparam/([a-z]+)', function($event, $param){
            return $param;
        }, array('name' => 'testEventSingleRegexParameter'));
        $this->assertEvent('regexparam/helloworld', null, 'helloworld');
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
        \prggmr\Engine::subscribe('multiregexparam/([a-z]+)/([a-z]+)', function($event, $param1, $param2){
            return $param1.$param2;
        }, array('name' => 'testEventWithMultipleRegexParameter'));
        $this->assertEvent('multiregexparam/hello/world', array(), 'helloworld');
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
        \prggmr\Engine::subscribe('multiparam2/([a-z]+)/([a-z]+)', function($event, $param1, $param2, $regex1, $regex2){
            return $param1.$param2.$regex1.$regex2;
        }, array('name' => 'testEventWithMultipleRegexAndMultipleSuppliedParamters'));
        $this->assertEvent('multiparam2/wor/ld', array('hel','lo'), 'helloworld');
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
        \prggmr\Engine::subscribe('simpleregex/:name', function($event, $name){
            return $name;
        }, array('name' => 'testRegexEventWithSimpleRegex'));
        $this->assertEvent('simpleregex/helloworld', array(), 'helloworld');
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
        \prggmr\Engine::subscribe('multisimpleregex/:name/:slug', function($event, $name, $slug){
            return $name.$slug;
        }, array('name' => 'testEventWithMultipleSimpleRegex'));
        $this->assertEvent('multisimpleregex/hello/world', array(), 'helloworld');
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
        \prggmr\Engine::subscribe('multisimpleregexparamsupplied/:name/:slug', function($event, $param1, $param2, $name, $slug){
            return $name.$param1.$slug.$param2;
        }, array('name' => 'testEventWithMultipleSimpleRegexAndSuppliedParameters'));
        $this->assertEvent('multisimpleregexparamsupplied/hel/wor', array('lo','ld'), 'helloworld');
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
        \prggmr\Engine::subscribe('simpleandregex/:name/([a-z]+)', function($event, $param1, $param2){
            return $param1.$param2;
        }, array('name' => 'testEventWithSimpleRegexAndRegexParameters'));
        $this->assertEvent('simpleandregex/hello/world', array(), 'helloworld');
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
        \prggmr\Engine::subscribe('simpleregexsupplied/:name/([a-z]+)', function($event, $param1, $param2, $param3){
            return $param2.$param1.$param3;
        }, array('name' => 'testEventWithSimpleRegexRegexAndSuppliedParameters'));
        $this->assertEvent('simpleregexsupplied/hel/ld', array('lowor'), 'helloworld');
    }


    /**
     * @expectedException LogicException
     */
    public function testException()
    {
        \prggmr\Engine::subscribe('exceptiontest', function($event){
            throw new Exception('This is a test!');
        }, array('name' => 'testException'));
        $this->assertEvent('exceptiontest', array(), array());
    }

    /**
     * Methods Covered
     * @Engine\version
     */
    public function testVersion()
    {
        $this->assertEquals(\prggmr\Engine::version(), PRGGMR_VERSION);
    }

    /**
     * Methods Covered
     * @Engine\flush
     */
    public function testFlush()
    {
        \prggmr\Engine::flush();
        $this->assertFalse(\prggmr\Engine::hasSubscriber('testSubscribe', 'subscriber'));
    }

    /**
     * Methods Covered
     * @Engine\registry
     *      @with array
     */
    public function testRegistryArray()
    {
        $registry = \prggmr\Engine::registry();
        $this->assertArrayHasKey('__data', $registry);
        $this->assertArrayHasKey('__libraries', $registry);
        $this->assertArrayHasKey('__events', $registry);
        $this->assertArrayHasKey('__debug', $registry);
        $this->assertArrayHasKey('__stats', $registry);
    }

    /**
     * Methods Covered
     * @Engine\registry
     *      @with array
     */
    public function testRegistryObject()
    {
        $registry = \prggmr\Engine::registry('object');
        $this->assertObjectHasAttribute('__data', $registry);
        $this->assertObjectHasAttribute('__libraries', $registry);
        $this->assertObjectHasAttribute('__events', $registry);
        $this->assertObjectHasAttribute('__debug', $registry);
        $this->assertObjectHasAttribute('__stats', $registry);
    }

    /**
     * Methods Covered
     * @Engine\subscribe
     *      @with option shift = true
     * @Engine\bubble
     *      @with option stackResults = true
     */
    public function testSubscriptionShift()
    {
        \prggmr\Engine::subscribe('shift_test', function($event){
            return 'Event1';
        });

        \prggmr\Engine::subscribe('shift_test', function($event){
            return 'Event2';
        });

        $this->assertEvent('shift_test', null, array('Event1','Event2'), array(
            'stackResults' => true
        ));

        \prggmr\Engine::flush();

        \prggmr\Engine::subscribe('shift_test', function($event){
            return 'Event1';
        });

        \prggmr\Engine::subscribe('shift_test', function($event){
            return 'Event2';
        }, array('shift' => true));

        $this->assertEvent('shift_test', null, array('Event2','Event1'), array(
            'stackResults' => true
        ));
    }

    /**
     * Methods Covered
     * @Engine\subscribe
     *      @with option force = true
     */
    public function testSubscriptionForce()
    {
        \prggmr\Engine::subscribe('force_test', function($event){
            return 'Force1';
        }, array('name' => 'ForceTest'));

        \prggmr\Engine::subscribe('force_test', function($event){
            return 'Force2';
        }, array('name' => 'ForceTest'));

        $this->assertEvent('force_test', null, 'Force1');

        \prggmr\Engine::subscribe('force_test', function($event){
            return 'Force2';
        }, array('name' => 'ForceTest', 'force' => true));

        $this->assertEvent('force_test', null, 'Force2');
    }

    /**
     * Methods Covered
     * @Engine\subscribe
     *      @with option namespace
     */
    public function testNamespace()
    {
        \prggmr\Engine::subscribe('namespace_test', function($event){
            return 'Namespace1';
        }, array('name' => 'NamespaceTest'));

        \prggmr\Engine::subscribe('namespace_test', function($event){
            return 'UnitTest1';
        }, array('name' => 'NamespaceTest', 'namespace' => 'UnitTest'));

        $this->assertEvent('namespace_test', null, 'Namespace1');
        $this->assertEvent('namespace_test', null, 'UnitTest1', array(
            'namespace' => 'UnitTest'
        ));
    }

    /**
     * Methods Covered
     * @Engine\subscribe
     *      @with option event
     */
    public function testEventReturn()
    {
        \prggmr\Engine::subscribe('object_test', function($event){
            return 'MyResults';
        });

        $bubble = \prggmr\Engine::bubble('object_test', null, array(
            'object' => true
        ));
        $this->assertInstanceOf('\prggmr\Event', $bubble);
    }
}