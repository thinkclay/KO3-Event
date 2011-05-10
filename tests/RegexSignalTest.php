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

class RegexSignalTest extends \PHPUnit_Framework_TestCase
{
    public function testRegexSignal()
    {
        $signal = new \prggmr\RegexSignal('hello.:world');
        $this->assertEquals('#hello.(?P<world>[\w_-]+)$#i', $signal->signal());
    }

    public function testRegexSignalMatch()
    {
        $signal = new \prggmr\RegexSignal('hello.:world');
        $this->assertEquals(array(
            'test'
        ), $signal->compare('hello.test'));
    }

    public function testRegexSignalMultipleMatches()
    {
        $signal = new \prggmr\RegexSignal('added.:action.:id.:from');
        $this->assertEquals(array(
            'user',
            '7',
            'administration'
        ), $signal->compare('added.user.7.administration'));
    }

    public function testRegexNoMatches()
    {
        $signal = new \prggmr\RegexSignal('regular.[\w_-]+');
        $this->assertTrue($signal->compare('regular.test'));
    }

    public function testRegexMatchesCombination()
    {
        $signal = new \prggmr\RegexSignal('test.([\w_-]+).(?<user>[\w_-]+).(.*)');
        $this->assertEquals(
            array(
                'one',
                'two',
                'three'
            ), $signal->compare('test.one.two.three'));
    }
}