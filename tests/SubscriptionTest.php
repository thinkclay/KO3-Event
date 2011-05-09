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

class SubscriptionTest extends \PHPUnit_Framework_TestCase
{
    public function testIdentifier()
    {
        $sub = new \prggmr\Subscription(function(){}, 'helloworld');
        $this->assertEquals('helloworld', $sub->getIdentifier());
    }

    public function testFire()
    {
        $sub = new \prggmr\Subscription(function(){
            return 'helloworld';
        }, 'helloworld');
        $this->assertEquals('helloworld', $sub->fire());
    }

    /**
     * @expectedException RuntimeException
     */
    public function testException()
    {
        $sub = new \prggmr\Subscription(function(){
            throw new \Exception(
                'I am an exception'
            );
        }, 'helloworld');
        $sub->fire();
    }
}