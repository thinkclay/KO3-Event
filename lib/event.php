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
 * @package  Prggmr
 * @category  Utilities
 * @copyright  Copyright (c), 2010 Nickolas Whiting
 */

use \prggmr\util as util;

/**
 * Event Object
 *
 * Represents an executed/executable event.
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
     * Flag that determains if an event is to return an array of results or
     * a single value.
     * Default: true : array
     *
     * @var  boolean  True | False
     */
    protected $_stackableResults = true;

    /**
     * Message associated with the current event state.
     *
     * @var  string  Message that is associated with current event state.
     */
    protected $_stateMessage = null;

    /**
     * Constructs a new event object.
     */
    public function __construct(Event $parent = null)
    {
        if (null !== $parent) {
            $this->_parent = $parent;
        }
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
     * Halts the current event stack, along with any subsequent event chains.
     *
     * @throws  LogicException  when attempting to set sequence halt while
     *          event is in an inactive state.
     *
     * @return  null
     */
    public function haltSequence(/* ... */)
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
    public function isHalted(/* ... */)
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
    public function getResults(/* ... */)
    {
        return $this->_return;
    }

    /**
     * Returns if this event can have a result stack rather than a single
     * returnable result.
     * This can set by calling `setResultsStackable()` method before
     * triggering an event.
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
     * Returns the event listener string this event will trigger upon.
     *
     * @return  object  Listener event will fire upon.
     */
    public function getListener(/* ... */)
    {
        return $this->_listener;
    }

    /**
     * Sets the listener for this event.
     */
    public function setListener($listener)
    {
        $this->_listener = $listener;
    }


    /**
     * Returns the parameters to pass to the event listeners
     *
     * @return  array  Array of parameters to pass to an event listener.
     */
    public function getParameters(/* ... */)
    {
        return $this->_params;
    }

    /**
     * Triggers the event chains attached for this event.
     *
     * @return  mixed  Results of the chain execution.
     */
    public function triggerChain(/* ... */)
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
     * Determains if this event has a parent sequence to call a chained event.
     *
     * @return  boolean  True | False
     */
    public function hasChain(/* ... */)
    {
        return (null !== $this->_parent);
    }

    /**
     * Triggers an event within the current scope.
     *
     * @param  array  $params  Parameters to directly pass to the event listener
     * @param  array  $options  Array of options usen trigger the event.
     * <ul>
     *         <li><strong>namespace</strong> - Namespace for event.
     *         Defaults to \prggmr::GLOBAL_DEFAULT.
     *         </li>
     *
     *         <li><strong>benchmark</strong> - Benchmark this events execution.</li>
     *
     *         <li><strong>flags</strong>  - Flags to pass to the `preg_match` function used for
     *         matching a regex event.</li>
     *
     *         <li><strong>offset</strong> - Specify the alternate place from which to start the search.
     *
     *         <li><strong>object</strong> - Return the event object.</li>
     *
     *         <li><strong>suppress</strong> - Suppress exceptions when an event is encountered in
     *         a STATE_ERROR.</li>
     * </ul>
     * @throws <ul><li>
     *          <strong>LogicException</strong> when an error is encountered during listener
     *          execution.</li>
     *          <li><strong>RuntimeException</strong> when attempting to execute an event in
     *          an unexecutable state.</li>
     *
     * @return  object  prggmr\util\Event
     * @see  prggmr::trigger
     */
    public function trigger(array $params = array(), array $options = array()) {
        return parent::trigger($this, $params, $options);
    }

    /**
     * __call overload method will attempt to call an event currently attatched
     * to the listenable object.
     * @see  \prggmr\util\listenable::trigger()
     */
    public function __call($event, array $args = array())
    {
        $defaults = array(0 => array(), 1 => array());
        $args += $defaults;
        $this->setListener($event);
        return $this->trigger($args[0], $args[1]);
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
}
