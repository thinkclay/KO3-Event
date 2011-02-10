<?php
namespace prggmr\util;
/******************************************************************************
 ******************************************************************************
 *   ##########  ##########  ##########  ##########  ####    ####  ##########
 *   ##      ##  ##      ##  ##          ##          ## ##  ## ##  ##      ##
 *   ##########  ##########  ##    ####  ##    ####  ##   ##   ##  ##########
 *   ##          ##    ##    ##########  ##########  ##        ##  ##    ##
 *******************************************************************************
 *******************************************************************************/

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
 * @package  Prggmr
 * @category  Record
 * @copyright  Copyright (c), 2010 Nickolas Whiting
 */

use \prggmr\util\data as data;

/**
 * Event Object
 *
 * Represents an executed event.
 *
 */
class Event extends Listenable
{

    /**
     * Event is actively being called.
     */
    const STATE_ACTIVE = 100;

    /**
     * Event is inactive and awaiting for its trigger.
     */
    const STATE_INACTIVE = 101;

    /**
     * Event has been called and finished execution.
     */
    const STATE_COMPLETE = 102;

    /**
     * Event has encountered a failure
     */
    const STATE_ERROR = 103;

    /**
     * Current state event in which this event is in.
     *
     * @var  array  Stack of event listeners.
     */
    protected $_state = Event::STATE_INACTIVE;

    /**
     * Results of any event listeners attatched to this event.
     *
     * @var  mixed  Results of event listener returns if any.
     */
    protected $_return = null;

    /**
     * Parent event which this event belongs if in a chain sequence.
     *
     * @var  object  \prggmr\util\Event
     */
    protected $_parent = null;

    /**
     * Halt the event que after this event finishes.
     *
     * @var  boolean  True to propagate | False otherwise
     */
    protected $_halt = false;

    /**
     * Listener in which this event will fire upon.
     *
     * @var  string  Name of event listener.
     */
    protected $_listener = null;

    /**
     * Flag that allows an event to return an array of results rather
     * than a single value.
     * Default: true
     *
     * @var  boolean  True | False
     */
    protected $_stackableResults = true;

    /**
     * Parameters that will be passed to the event listeners.
     *
     * @var  array  Array of parameters to pass to event listeners.
     */
    protected $_params = array();

    /**
     * Message associated with the current event state.
     *
     * @var  string  Message that is associated with current event state.
     */
    protected $_stateMessage = null;

    /**
     * Constructs a new event object.
     */
    public function __construct($event, Event $parent = null)
    {
        if (null !== $parent) {
            $this->_parent = $parent;
        }
        $this->setState(self::STATE_INACTIVE);
        $this->_listener = $event;
    }

    /**
     * Sets the event state.
     *
     * @param  integer  $state  State this event is currently in.
     * @param  string  $msg  Message to associate with this event state.
     *
     * @throws  InvalidArgumentException when invalid state is given.
     *
     * @return  boolean  True on success
     */
    public function setState($state, $msg = null)
    {
        if ($state > 104 || $state < 100) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Invalid event state "%s" cannot set state on event %s',
                    $state,
                    $this->_name
                )
            );
        }
        $this->_state = $state;
        $this->_stateMessage = $msg;
        return true;
    }

    /**
     * Returns the current event state message.
     *
     * @return  mixed  Current event state message, NULL otherwise.
     */
    public function getStateMessage()
    {
        return $this->_stateMessage;
    }

    /**
     * Returns the current event state.
     *
     * @return  integer  Current state of this event.
     */
    public function getState()
    {
        return $this->_state;
    }

    /**
     * Halts the current event stack, along with any subsequent event chains.
     *
     * @throws  LogicException  when attempting to set sequence halt while event is
     *          in an inactive state.
     *
     * @return  null
     */
    public function haltSequence()
    {
        if ($this->getState() === self::STATE_ACTIVE) {
            $this->_halt = true;
        } else {
            throw new \LogicException(
                sprintf(
                    "Event sequence cannot be halted while event is not in an active state"
                )
            );
        }
    }

    /**
     * Returns the flag to halt the event stack once this
     * event completes execution.
     *
     * @return  boolean  True to halt | False otherwise
     */
    public function isHalted()
    {
        return $this->_halt;
    }

    /**
     * Returns the current return value of this event.
     *
     * This method should also be used to detect if this event currently
     * has results previously attatched by a listener in the same stack, to
     * avoid overwritting results.
     *
     * @return  mixed  Results of
     */
    public function getResults()
    {
        return $this->_return;
    }

    /**
     * Returns if this event can have a result stack rather than a single
     * returnable result.
     * This can set by calling `makeResultsStackable()` method before
     * triggering an event.
     *
     * @return  boolean  True | False
     */
    public function isResultsStackable()
    {
        return $this->_stackableResults;
    }

    /**
     * Sets this event to allow a stack return, allowing multiple results
     * rather than a single value.
     * It must be noted that this must be set before an event begins firing
     * or this flag will be ignored, also once set the results will allways
     * be returned within an array.
     *
     * @param  boolean  $flag  True to allow | False otherwise.
     *
     * @return  boolean  True on success | False otherwise
     */
    public function setResultsStackable($flag)
    {
        $flag = (boolean) $flag;
        if ($this->getState() !== self::STATE_INACTIVE) {
            return false;
        }
        $this->_stackableResults = $flag;
        return true;
    }

    /**
     * Sets the value that will be returned by `getResults`.
     *
     * @param  mixed  $return  Value to set as the result of this event.
     *
     * @return  boolean  True
     */
    public function setResults($return)
    {
        if ($this->isResultsStackable()) {
            if (null === $this->_return) {
                $this->_return = array();
            }
            if (!is_array($this->_return)) {
                (array) $this->_return[] = $return;
            } else {
                $this->_return[] = $return;
            }
        } else {
            // Blindly overwrite the return of this event
            $this->_return = $return;
        }

        return true;
    }

    /**
     * Returns the event listener string this event will trigger upon.
     *
     * @return  object  Listener event will fire upon.
     */
    public function getListener()
    {
        return $this->_listener;
    }

    /**
     * Returns the parameters to pass to the event listeners
     *
     * @return  array  Array of parameters to pass to an event listener.
     */
    public function getParameters()
    {
        return $this->_params;
    }

    /**
     * Triggers the event chains attached to this event.
     *
     * @return  mixed  Results of the chain execution.
     */
    public function triggerChain()
    {
        if ($this->haltSequence()) {
            return false;
        }

        if (null !== $this->_parent) {

            // Ensure we are in an active state
            if (self::STATE_ACTIVE !== $this->getState()) {
                $this->setState(self::STATE_ACTIVE);
            }
            $this->_parent->trigger();
            return $this->_parent->getResults();
        }
    }

    /**
     * Determains if this event has a parent sequence to init a chained event.
     *
     * @return  boolean  True | False
     */
    public function hasChain()
    {
        return (null !== $this->_parent);
    }

    /**
     * Triggers this event to notify all listeners.
     *
     * @param  string  $event  Name of the event to trigger
     * @param  array  $params  Parameters to directly pass to the event listener
     * @param  array  $options  Array of options. Avaliable options.

     *         `namespace` - Namespace for event.
     *         [Default: Class name]
     *
     *         `benchmark` - Benchmark this events execution.
     *
     *         `flags`  - Flags to pass to the `preg_match` function used for
     *         matching a regex event.
     *
     *         `offset` - Specify the alternate place from which to start the
     *         regex search.
     *
     *         `errors` - Throws an exception if any listener returns false.
     *
     * @throws  RuntimeException  if `errors` option is `true` and a listener
     *          returns false.
     *
     * @return  array|boolean  Array of listeners' results, `true` when no
     *          listeners triggered.
     */
    public function trigger(array $params = array(), array $options = array()) {
        return parent::trigger($this, $params, $options);
    }
}
