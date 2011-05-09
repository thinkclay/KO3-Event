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

use \Countable,
    \Iterator,
    \InvalidArgumentException;


/**
 * The queue object is a priority queue implemented using a heap, it was decided
 * aganist using PHP's implementation of the current PriorityQueue which is not 
 * to say it isn't useful, only wasteful. This does come at a disadvantage of
 * sacrificing performance over functionality ... even at a small cost. 
 *
 * The priority works as a min-heap, which also bring the point that unlike 
 * the implementation in the SPL priority is limited only to integers this is 
 * done for performance reasons since sorting integers will be faster than
 * any other method.
 *
 * The heap is implemented using only the priority, the data is ignored.
 *
 * The object itself represents the queue of subscriptions for an event Signal,
 * which is passed as the constructors first parameter.
 */
class Queue implements Countable, Iterator {
    
    /**
     * The event signal for which this queue is attaching subscriptions.
     * 
     * @var  object  Signal
     */
    protected $_signal = null;
    
    /**
     * The queue data
     *
     * @var  array
     */
     protected $_data = array();
     
    /**
     * Constructs a new queue object.
     *
     * @param  object  $signal  Signal
     *
     * @return  \prggmr\Queue
     */ 
    public function __construct(Signal $signal)
    {
        $this->_signal = $signal;
    }
    
    /**
     * Returns the event signal this queue represents.
     *
     * @return  object
     */
    public function getSignal(/* ... */)
    {
        return $this->_signal;
    }
    
    /**
     * Inserts a subscription into the queue.
     *  
     * @param  object  $subscription  \prggmr\Subscription
     * @param  integer $priority  Priority of the subscription
     * 
     * @return  void
     */
    public function enqueue(Subscription $subscription, $priority = 1)
    {
        // force int
        $priority = (integer) $priority;
        $this->_data[] = array(
            $subscription, $priority
        );
        $this->_prioritize();
    }
     
    /**
    * Removes a subscription from the queue.
    *
    * @param  mixed  subscription  String identifier of the subscription or
    *         a Subscription object.
    * 
    * @throws  InvalidArgumentException
    * @return  void
    */
    public function dequeue($subscription)
    {
        if (is_string($ubscription)) {
            $subscription = $this->locate($subscription);
        }
        
        
    }
     
    /**
    * Locates a subscription in the queue by the identifier.
    * 
    * @param  string  $identifier  String identifier of the subscription
    * 
    * @return  mixed  Subscription object | False otherwise
    */
    public function locate($identifier)
    {
        while($this->valid()) {
            if ($this->current()->getIdentifier() == $identifier) {
                break;
            }
            $this->next();
        }
        // we cannot rewind and return the current forcing to insert into a var
        $sub = $this->current();
        $this->rewind();
        return $sub;
    }
    
    /**
     * Returns the number of subscriptions in the queue.
     *
     * @return  integer
     */
    public function count(/* ... */)
    {
        return count($this->_data);
    }
     
    /**
     * Moves the internal iterator to the next position.
     * 
     * @return  void
     */
    public function next(/* ... */)
    {
        next($this->_data);
    }
    
    /**
     * Returns the current iterator position.
     *
     * @return  integer
     */
    public function key(/* ... */)
    {
        return key($this->_data);
    }
    
    /**
     * Returns if the current position is valid.
     *
     * @return  boolean
     */
    public function valid(/* ... */)
    {
        return isset($this->_data[$this->key()]);
    }
    
    /**
     * Rewinds the iterator to position 0.
     *
     * @return  void
     */
    public function rewind(/* ... */)
    {
        reset($this->_data);
    }
    
    /**
     * Returns the data at the current iterator position. 
     * 
     * @return  array
     */
    public function current(/* ... */)
    {
        return current($this->_data);
    }
     
    /**
     * Prioritizes the queue.
     *
     * @return  void
     */
    protected function _prioritize(/* ... */)
    {
        $tmp = array();
        foreach ($this->_data as $_k => $_v) {
            if (!isset($tmp[$_v[1]])) {
                $tmp[$_v[1]] = array();
            }
            $tmp[$_v[1]][] = $_v;
        }
        ksort($tmp, SORT_NUMERIC);
        $this->_data = array();
        foreach ($tmp as $_array) {
            foreach ($_array as $_sub) {
                $this->_data[] = $_sub;
            }
        }
    }
}