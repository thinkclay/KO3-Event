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

use \Closure;

/**
 * The subscriber object is the main object responsible for holding our event
 * bubblers or the function which will execute when our queue says it is time.
 * 
 * Though attached to a subscriptton queue the object itself contains no
 * information on what subscription it belongs to, it is possible to couple
 * it into the object if it is really needed but realistically the bubble will
 * recieve of copy of the current event which will ironically contain the 
 * subscription object that this event is contained within allowing it to 
 * call the event that is currently firing ... and if this seems a bit
 * crazy well thats because it is.
 */
class Subscription {
    
    /**
     * The lambda function that will execute when this subscription is
     * triggered.
     */
    protected $_function = null;

    /**
     * String identifier for this subscription
     *
     * @var  string
     */
     protected $_identifier = null;
    
    /**
     * Constructs a new subscription object.
     *
     * @param  object  $function  \Closure
     *
     * @return  \prggmr\Queue
     */ 
    public function __construct(\Closure $function, $identifier)
    {
        $this->_function = $function;
        $this->_identifier = (string) $identifier;
    }
    
    /**
     * Fires this subscriptions function.
     * 
     * @return  mixed  Results of the function
     */
     public function fire()
     {
         return call_user_func_array($this->_function, func_get_args());
     }

     /**
     * Returns the identifier.
     * 
     * @return  string
     */
     public function getIdentifier()
     {
         return $this->_identifier;
     }
}
