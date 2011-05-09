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

class QueueTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->queue = new \prggmr\Queue(new \prggmr\Signal('helloworld'));
    }

    public function tearDown()
    {
        unset($this->queue);
    }

    public function testQueueSignal()
    {
        $this->assertInstanceOf('\prggmr\Signal', $this->queue->getSignal());
        $this->assertEquals('helloworld', $this->queue->getSignal(true));
    }

    public function testEnqueueAndCount()
    {
        $this->queue->enqueue(new \prggmr\Subscription(function(){}, 'test'));
        $this->assertEquals(1, $this->count());
    }

    public function testDequeueStringIdentifier()
    {
        $this->queue->enqueue(new \prggmr\Subscription(function(){}, 'test'));
        $this->assertEquals(1, $this->count());
        $this->assertTrue(!$this->queue->dequeue('test'));
        $this->assertEquals(0, $this->queue->count());
    }

    public function testDequeueObject()
    {
        $sub = new \prggmr\Subscription(function(){}, 'test');
        $this->queue->enqueue($sub);
        $this->assertEquals(1, $this->count());
        $this->assertTrue(!$this->queue->dequeue($sub));
        $this->assertEquals(0, $this->queue->count());
    }

    public function testRewindKeyNext()
    {
        $this->queue->enqueue(new \prggmr\Subscription(function(){}, 'test1'), 10);
        $this->queue->enqueue(new \prggmr\Subscription(function(){}, 'test2'), 10);
        $this->assertEquals(0, $this->queue->key());
        $this->queue->next();
        $this->assertEquals(1, $this->queue->key());
        $this->queue->rewind();
        $this->assertEquals(0, $this->queue->key());
    }
}