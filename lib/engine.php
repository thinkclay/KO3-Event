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
     * NOTE: Passing an array as the signal parameter should be done only
     *       once per subscription que as each time a new Queue is created.
     *
     *
     * @param  mixed  $signal  Signal the subscription will attach to, this
     *         can be a Signal object, the signal representation or an array
     *         for a chained signal.
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

        if (is_array($signal) && isset($signal[0]) && isset($signal[1])) {
            $queue = $this->queue($signal[0]);
			$chain = $this->queue($signal[1]);
            $queue->getSignal()->setChain($signal[1]);
            return $queue->enqueue($subscription, $priority);
        } else {
            return $this->queue($signal)->enqueue($subscription, $priority);
        }
    }
	
	/**
    * Removes a subscription from the queue.
    *
    * @param  mixed  $signal  Signal the subscription is attached to, this
     *         can be a Signal object or the signal representation.
    *
    * @param  mixed  subscription  String identifier of the subscription or
    *         a Subscription object.
    *
    * @throws  InvalidArgumentException
    * @return  void
    */
    public function dequeue($signal, $subscription)
    {
		$queue = $this->queue($signal, false);
		if (false === $queue) return false;
		return $queue->dequeue($subscription);
	}

    /**
     * Locates a Queue object in storage, if not found one is created.
     *
     * @param  mixed  $signal  Signal the queue represents.
     * @param  boolean  $generate  Generate the queue if not found.
     *
     * @return  mixed  Queue object, false if generate is false and queue
     *          is not found.
     */
    public function queue($signal, $generate = true)
    {
        $obj = (is_object($signal) && $signal instanceof Signal);

        $this->_storage->rewind();
        while($this->_storage->valid()) {
            if (($obj && $this->_storage->current()->getSignal() === $signal) ||
                ($this->_storage->current()->getSignal(true) === $signal)) {
                return $this->_storage->current();
            }
            $this->_storage->next();
        }
		
		if (!$generate) return false;
		
        if (!$obj) {
            $signal = new Signal($signal);
        }

        $obj = new Queue($signal);

        // new queue
        $this->_storage->attach($obj);
        return $obj;
    }

    /**
     * Fires an event signal.
     *
     * @param  mixed  $signal  The event signal, this can be the signal object
     *         or the signal representation.
     *
     * @param  array  $vars  Array of variables to pass the subscribers
     *
     * @param  object  $event  Event
     *
     * @return  object  Event
     */
    public function fire($signal, $vars = null, $event = null)
    {
		$compare = false;
        $this->_storage->rewind();
        while($this->_storage->valid()) {
			// compare the signal given with the queue signal ..
			// TODO: Currently this allows for the first signal match to be used
			// this should allow for either it to continue on with itself until
			// it finds the signal it wants based on some crazy algorithm that
			// has yet to be written OR use every signal it compares with
            if (false !== ($compare = $this->_storage->current()->getSignal()->compare($signal))) {
                break;
            }
            $this->_storage->next();
        }

        if (false === $compare) {
            return false;
        }
		
		if (null !== $vars) {
			if (!is_array($vars)) {
				$vars = array($vars);
			}
		}

        $queue = $this->_storage->current();
		// rewinds and prioritizes the queue
        $queue->rewind();

        if (!is_object($event)) {
            $event = new Event($queue->getSignal());
        } elseif (!$event instanceof Event) {
            throw new \InvalidArgumentException(
                sprintf(
                    'fire expected instance of Event recieved "%s"'
                , get_class($event))
            );
        }

        $event->setSignal($queue->getSignal());
        $event->setState(Event::STATE_ACTIVE);

        if (count($vars) === 0) {
            $vars = array(&$event);
        } else {
            $vars = array_merge(array(&$event), $vars);
        }

        if ($compare !== true) {
            // allow for array return
            if (is_array($compare)) {
                $vars = array_merge($vars, $compare);
            } else {
                $vars[] = $compare;
            }
        }

		// the main loop
        while($queue->valid()) {
            if ($event->isHalted()) break;
            $queue->current()->fire($vars);
            if ($event->getState() == Event::STATE_ERROR) {
                throw new \RuntimeException(
                    sprintf(
                        'Event execution failed with message "%s"',
                        $event->getStateMessage()
                    )
                );
            }
            $queue->next();
        }

        // the chain
        if (null !== ($chain = $queue->getSignal()->getChain())) {
            if (null !== ($data = $event->getData())) {
                // remove the current event from the vars
                unset($vars[0]);
                $vars = array_merge($vars, $event->getData());
            }
            $chain = $this->fire($chain, $vars);
            if (false !== $chain) {
                $event->setChain($chain);
            }
        }

        // keep the event in an active state until its chain completes
        $event->setState(Event::STATE_INACTIVE);

        return $event;
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

    /**
     * Flushes the engine.
     */
    public function flush(/* ... */)
    {
        $this->_storage = new \SplObjectStorage();
    }

    /**
     * Returns the count of subsciption queues in the engine.
     *
     * @return  integer
     */
    public function count()
    {
        return $this->_storage->count();
    }
}