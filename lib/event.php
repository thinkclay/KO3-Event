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

/**
 * Event
 *
 * Represents an executed/executable prggmr event.
 */
class Event
{
    /**
     * Event is actively being called.
     */
    const STATE_ACTIVE = 0x01;

    /**
     * Event is inactive and awaiting for its bubble.
     */
    const STATE_INACTIVE = 0x02;

    /**
     * Event has encountered a failure
     */
    const STATE_ERROR = 0x03;

    /**
     * Current state event in which this event is in.
     *
     * @var  int
     */
    protected $_state = Event::STATE_INACTIVE;

    /**
     * Results returned after this event bubbles.
     *
     * @var  mixed
     */
    protected $_return = null;

    /**
     * Halt the event que after this event finishes.
     *
     * @var  boolean  True to propagate | False otherwise
     */
    protected $_halt = false;

    /**
     * Signal this event represents.
     *
     * @var  object  Signal
     */
    protected $_signal = null;

    /**
     * Flag that allows the results from each subscriber to stack and return
     * an array, rather than overwriting the current return each time a new
     * is recieved.
     *
     * @var  boolean  True | False
     */
    protected $_stackableResults = true;

    /**
     * Message associated with the current event state.
     *
     * @var  string
     */
    protected $_stateMessage = null;

    /**
     * Event which was chained from this event.
     *
     * @param  object  Event
     */
    protected $_chain = null;

    /**
     * Constructs a new event object.
     */
    public function __construct()
    {
		// default event state
        $this->setState(self::STATE_INACTIVE);
    }

    /**
     * Sets the event state.
     *
     * @param  integer  $state  State this event is currently in.
     * @param  string  $msg  Message to associate with this event state.
     *
     * @return  boolean  True on success
     */
    public function setState($state, $msg = null)
    {
        $this->_state = (int) $state;
        $this->_stateMessage = $msg;
        return true;
    }

    /**
     * Returns the current event state message.
     *
     * @return  mixed  Current event state message, NULL otherwise.
     */
    public function getStateMessage(/* ... */)
    {
        return $this->_stateMessage;
    }

    /**
     * Returns the current event state.
     *
     * @return  integer  Current state of this event.
     */
    public function getState(/* ... */)
    {
        return $this->_state;
    }

    /**
     * Halts or prevents an event stack including chains.
     *
     * @return  void
     */
    public function halt(/* ... */)
    {
        $this->_halt = true;
    }

    /**
     * Returns the flag to halt the event stack once this
     * event completes execution.
     *
     * @return  boolean  True to halt | False otherwise
     */
    public function isHalted(/* ... */)
    {
        return $this->_halt;
    }

    /**
     * Returns the current return value of this event.
     *
     * This method should also be used to detect if this event currently
     * has results previously attatched by a subscriber in the same stack to
     * avoid overwritting results.
     *
     * @return  mixed  Results of
     */
    public function getResults(/* ... */)
    {
        return $this->_return;
    }

    /**
     * Returns if this event can have a result stack rather than a single
     * returnable result.
     * This can set by calling `setResultsStackable()` method before
     * bubbling an event.
     *
     * @return  boolean  True | False
     */
    public function isResultsStackable(/* ... */)
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
     * Returns the event subscription string this event will bubble upon.
     *
     * @return  string
     */
    public function getSignal(/* ... */)
    {
        return $this->_signal;
    }

    /**
     * Sets the signal this event represents.
     *
     * @param  object  $signal  Signal
     *
     * @return  void
     */
    public function setSignal($signal)
    {
        $this->_signal = $signal;
    }

    /**
     * Empties the current return results.
     *
     * @return  void
     */
    public function clearResults()
    {
        $this->_return = null;
    }

    /**
     * Sets the chained event.
     *
     * @param  object  $chain  Event
     */
    public function setChain(Event $chain)
    {
        $this->_chain = $chain;
    }

    /**
     * Returns the chained Event object if exists.
     *
     * @return  mixed  Event object, null if no chain exists.
     */
    public function getChain()
    {
        return $this->_chain;
    }
}