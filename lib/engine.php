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


use \SplObjectStorage,
    \Closure,
    \InvalidArgumentException;

/**
 * The engine object rewritten serves now as much less the workhorse
 * of the system and rather the moderator as it technically should.
 *
 * The engine is responsible for ensuring all data passed into the system
 * meets the specifications allowing it to be intrepreted by each module.
 *
 * The engine still supports the main methods of bubbling and subscripting
 * only it now performs almost no logic other than type checking.
 */
class Engine extends Singleton {

    /**
     * The storage of Queues.
     *
     * @var  object  SplObjectStorage
     */
    private $_storage = null;

    /**
     * Construction inits our blank object storage, nothing else.
     *
     * @return  void
     */
    public function __construct(/* ... */)
    {
        $this->_storage = new \SplObjectStorage();
    }

    /**
     * Attaches a new subscription to a signal queue.
     *
     * @param  mixed  $signal  Signal the subscription will attach to, this
     *         can be a Signal object or the signal represented.
     *
     * @param  mixed  $subscription  Subscription closure that will trigger on
     *         fire or a Subscription object.
     *
     * @param  mixed  $identifier  String identifier for this subscription, if
     *         an integer is provided it will be treated as the priority.
     *
     * @param  mixed  $priority  Priority of this subscription within the Queue
     *
     * @throws  InvalidArgumentException  Thrown when an invalid callback is
     *          provided.
     *
     * @return  void
     */
    public function subscribe($signal, $subscription, $identifier = null, $priority = null)
    {
        if (is_int($identifier)) {
            $priority = $identifier;
        }

        if (!$subscription instanceof Subscription) {
            if (!is_callable($subscription)) {
                throw new \InvalidArgumentException(
                    'subscription callback is not a valid callback'
                );
            }
            $subscription = new Subscription($subscription, $identifier);
        }

        return $this->_queue($signal)->enqueue($subscription, $priority);
    }

    /**
     * Locates a Queue object in storage, if not found one is created.
     *
     * @param  mixed  $signal  Signal the queue represents.
     *
     * @return  object  Queue
     */
    public function _queue($signal)
    {
        $obj = (is_object($signal) && $signal instanceof Signal);

        $this->_storage->rewind();
        while($this->_storage->valid()) {
            if (($obj && $this->_storage->current->getSignal() === $signal) ||
                ($this->_storage->current()->getSignal(true) === $signal)) {
                return $this->current();
            }
            $this->_storage->next();
        }

        if (!$obj) {
            $obj = new Signal($signal);
        }

        // new queue
        $this->_storage->attach(new Queue($obj));
        return $this->_storage[$obj];
    }

    /**
     * Returns the current version of prggmr.
     *
     * @return  string
     */
    public static function version(/* ... */)
    {
        return PRGGMR_VERSION;
    }
}