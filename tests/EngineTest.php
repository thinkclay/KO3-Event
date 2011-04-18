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
        \prggmr\Engine::subscribe('subscriber', function($event){}, array('name' => 'firsttest'));
        $this->assertTrue(\prggmr\Engine::hasSubscriber('firsttest', 'subscriber'));
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
        });
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
        });
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
        \prggmr\Engine::subscribe('regexparam-([a-z]+)', function($event, $slug){
            return $slug;
        });
        $this->assertEvent('regexparam-helloworld', array(), 'helloworld');
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
        \prggmr\Engine::subscribe('multiregexparam-([a-z]+)-([a-z]+)', function($event, $param1, $param2){
            return $param1.$param2;
        });
        $this->assertEvent('multiregexparam-hello-world', array(), 'helloworld');
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
        \prggmr\Engine::subscribe('multiparam2-([a-z]+)-([a-z]+)', function($event, $param1, $param2, $regex1, $regex2){
            return $param1.$param2.$regex1.$regex2;
        });
        $this->assertEvent('multiparam2-hello-world', array('hel','llo'), 'helloworld');
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
        \prggmr\Engine::subscribe('simpleregex-:name', function($event, $name){
            return $name;
        });
        $this->assertEvent('simpleregex-helloworld', array(), 'helloworld');
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
        \prggmr\Engine::subscribe('simpleregex-:name-:slug', function($event, $name, $slug){
            return $name.$slug;
        });
        $this->assertEvent('multisimpleregex-hello-world', array(), 'helloworld');
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
        \prggmr\Engine::subscribe('multisimpleregexparamsupplied-:name-:slug', function($event, $param1, $param2, $name, $slug){
            return $param1.$name.$param2.$slug;
        });
        $this->assertEvent('multisimpleregexparamsupplied-hel-wor', array('lo','ld'), 'helloworld');
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
        \prggmr\Engine::subscribe('simpleandregex-:name-([a-z]+)', function($event, $param1, $param2){
            return $param1.$param2;
        });
        $this->assertEvent('simpleandregex-hello-world', array(), 'helloworld');
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
        \prggmr\Engine::subscribe('simpleandregex-:name-([a-z]+)', function($event, $param1, $param2, $param3){
            return $param1.$param2.$param3;
        });
        $this->assertEvent('simpleandregex-hel-ld', array('lowor'), 'helloworld');
    }
    
    
    /**
     * @expectedException LogicException
     */
    public function testException()
    {
        \prggmr\Engine::subscribe('exceptiontest', function($event){
            throw new Exception('This is a test!');
        });
        $this->assertEvent('exceptiontest', array(), array());
    }
  
}