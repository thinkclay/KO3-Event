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

use \SplPriorityQueue,
    \BadMethodCallException,
    \InvalidArgumentException;


/**
 * The Queue object is a queue based object using the SplPriorityQueue stdobject
 * developed into PHP 5.3. The object is a basic representation of an
 * event that will be called by the engine. 
 */
class Queue extends SplPriorityQueue {
    
    protected $_event = null;
    
    protected $_serial = PHP_INT_MAX;
    
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
        return parent::insert($subscription, array(
                $priority, 
                $this->_serial--
            )
        );
    }
     
    /**
    * Removes a subscription from the queue.
    * 
    * @todo 
    * Currently this method does not function providing no means to remove a 
    * subscription from the queue as such functionality is reasonably 
    * useful it needs to be implemented.
    *
    * @param  mixed  subscription  String identifier of the subscription or
    *         a Subscription object.
    * 
    * @throws  InvalidArgumentException
    * @return  void
    */
    public function dequeue($subscription)
    {}
     
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
    * Override insert method and force enqueue use.
    *
    * @throws  BadMethodCallException
    */
    public function insert($value, $priority) 
    {
       throw new \BadMethodCallException(
           'insert method is disallowed use enqueue instead'
       );
    }
}

$queue = new Queue('my_event');
$queue->insert();
