<?php
namespace prggmr;
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

use \SplQueue,
    \InvalidArgumentException;


/**
 * The Queue object is a queue based object using the SplQueue std object
 * developed into PHP 5.3. The object is a basic representation of an
 * event that will be called by the engine. 
 */
class Queue extends SplQueue {
    
    protected $_event = null;
    
    /**
     * Constructs a new queue object.
     *
     * @param  string  $event  Event this queue represents
     *
     * @return  \prggmr\Queue
     */ 
    public function __construct($event)
    {
        if (null === $event) {
            throw new InvalidArgumentException(
                'Required parameter "event" not supplied'
            );
        }
        $this->_event = $event;
    }
    
    /**
     * Returns the event this queue represents.
     *
     * @return  string
     */
    public function getEvent()
    {
        return $this->_event;
    }
}
