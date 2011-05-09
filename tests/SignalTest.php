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
 * \prggmr\Event Unit Tests
 */

include_once 'bootstrap.php';

class SignalTest extends \PHPUnit_Framework_TestCase
{
    public function testSignal()
    {
        $signal = new \prggmr\Signal('helloworld');
        $this->assertEquals('helloworld', $signal->signal());
    }

    public function testStringSignal()
    {
        $signal = new \prggmr\Signal('helloworld');
        $this->assertTrue($signal->compare('helloworld'));
        $this->assertFalse($signal->compare('HelloWorld'));
    }

    public function testArraySignal()
    {
        $signal = new \prggmr\Signal(array(
            0 => 'helloworld'
        ));
        $this->assertTrue($signal->compare(array(
            0 => 'helloworld'
        )));
        $this->assertFalse($signal->compare(array(
            0 => 'HellOworld'
        )));
    }

    public function testObjectSignal()
    {
        $obj = new \stdClass();
        $obj->hello = 'world';
        $signal = new \prggmr\Signal($obj);
        $this->assertTrue($signal->compare($obj));
        $obj = new \stdClass();
        $obj->hello = 'wORld';
        $this->assertFalse($signal->compare($obj));
    }

    public function testTrueSignal()
    {
        $signal = new \prggmr\Signal(true);
        $this->assertTrue($signal->compare(true));
        $this->assertFalse($signal->compare(1));
        $this->assertFalse($signal->compare(''));
    }

    public function testFalseSignal()
    {
        $signal = new \prggmr\Signal(false);
        $this->assertTrue($signal->compare(false));
         $this->assertFalse($signal->compare(0));
    }

    public function testIntegerSignal()
    {
        $signal = new \prggmr\Signal(100);
        $this->assertTrue($signal->compare(100));
        $this->assertFalse($signal->compare('100'));
    }

    public function testFloatSignal()
    {
        $signal = new \prggmr\Signal(100.2);
        $this->assertTrue($signal->compare(100.2));
        $this->assertFalse($signal->compare('100.2'));
    }
}