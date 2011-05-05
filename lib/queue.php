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
    
    protected $serial = PHP_INT_MAX;
    
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
     public function enqueue(Subscription $subscription, $priority)
     {
        return parent::insert($data, array($priority, $this->serial--));
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
        if (is_string($subscription)) {
            $subscription = $this->locate($subscription);
        } 
        
        if (!is_object($subscription) || 
                   !$subscription instanceof Subscription) {
            throw new \InvalidArgumentException(
                sprintf('Expected string or instance of Subscription recieved %s',
                (is_object($subscription) ? get_class($subscription) : 
                gettype($subscription))
                )
            );       
        }
        
        while ($this->next()) {
            if ($this->current() === $subscription) {
                unset($this->current());
            }
        }
        
        $this->rewind();
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
          while($this->next()) {
              if ($this->current()->getIdentifier() == $identifier) {
                    return $this->current();
              }
          }
          $this->rewind();
          return false;
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
