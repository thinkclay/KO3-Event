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

class EventTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->event = new \prggmr\Event();
    }

    public function tearDown()
    {
        unset($this->event);
    }

    /**
     * Test Event states
     */
    public function testEventStates()
    {
        $this->assertEquals(\prggmr\Event::STATE_INACTIVE, $this->event->getState());
        $this->event->setState(\prggmr\Event::STATE_ACTIVE);
        $this->assertEquals(\prggmr\Event::STATE_ACTIVE, $this->event->getState());
    }

    /**
     * Test event state messages
     */
    public function testEventStateMessage()
    {
        $this->assertEquals(\prggmr\Event::STATE_INACTIVE, $this->event->getState());
        $this->event->setState(\prggmr\Event::STATE_ACTIVE, 'This is a test');
        $this->assertEquals('This is a test', $this->event->getStateMessage());
    }
}