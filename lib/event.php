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
    const STATE_ACTIVE = 0xCF60;

    /**
     * Event is inactive and awaiting for its bubble.
     */
    const STATE_INACTIVE = 0x5250;

    /**
     * Event has encountered a failure
     */
    const STATE_ERROR = 0x2F4B;

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
     * Results from a chained event
     *
     * @var  mixed
     */
    protected $_chainReturn = null;

    /**
     * Event object to call when bubbling the event chain.
     *
     * @var  object  \prggmr\util\Event
     */
    protected $_chain = null;

    /**
     * Halt the event que after this event finishes.
     *
     * @var  boolean  True to propagate | False otherwise
     */
    protected $_halt = false;

    /**
     * Subscriber in which this event will bubble upon.
     *
     * @var  string  Name of event subscriber.
     */
    protected $_subscription = null;

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
    public function getSubscription(/* ... */)
    {
        return $this->_subscription;
    }

    /**
     * Sets the subscription string for this event.
     *
     * @param  string  $str  String name to subscribe this event.
     *
     * @return  void
     */
    public function setSubscription($str)
    {
        $this->_subscription = $str;
    }

	/**
	 * Sets the event chain.
	 *
	 * @todo  Possibly a method of establishing event chains based on dynamic
	 * 		  data related to the event/chain sequence.
	 *
	 *
	 * @param  object  $event  Event object to bubble in chain
	 *
	 * @return  void
	 */
	public function setChain(Event $event)
	{
		$this->_chain = $event;
		return null;
	}

    /**
     * Bubbles the event chains attached for this event.
     *
     * @param  boolean  stateCheck  Boolean to check the event state, setting
     * 		   false will skip the check allowing for a chained event sequence
     * 		   while the event is in any state. Otherwise the event will be
     * 		   forced into an active state.
     *
     * @return  mixed  Results of the chain execution.
     */
    public function executeChain($stateCheck = true)
    {
        if ($this->isHalted()) {
            return false;
        }

        if ($this->hasChain()) {

            // Ensure we are in an active state
            if ($stateCheck && self::STATE_ACTIVE !== $this->getState()) {
                $this->setState(self::STATE_ACTIVE);
            }
            $this->_chain->bubble();
            return $this->_chain->getResults();
        }
    }

    /**
     * Determains if this event has a chain sequence to call.
     *
     * @return  boolean  True | False
     */
    public function hasChain(/* ... */)
    {
        return (null !== $this->_chain);
    }

    /**
     * Bubbles an event.
     *
     * @param  array  $params  Parameters to directly pass to the event subscriber
     * @param  array  $options  Array of options. Avaliable options
     *
     *         `namespace` - `namespace` - Namespace for event.
     *         Defaults to Engine::GLOBAL_DEFAULT.
     *
     *         `benchmark` - Benchmark this events execution.
     *
     *         `offset` - Specify the alternate place from which to start the search.
     *
     *         `object` - Return the event object.
     *
     *         `suppress` - Suppress exceptions when an event is encountered in
     *         a STATE_ERROR.
     *
     *         `stateCheck` -
     *
     * @throws  LogicException when an error is encountered during subscriber
     *          execution
     *          RuntimeException when attempting to execute an event in
     *          an unexecutable state
     *
     * @return  mixed  Results of event
     * @see  Engine::bubble
     */
    public function bubble(array $params = array(), array $options = array()) {
		$defaults = array('stateCheck' => true);
		$options += $defaults;
		//bubble chain if exists
		$this->_chainReturn = $this->executeChain($options['stateCheck']);
        return Engine::bubble($this, $params, $options);
    }

    /**
     * Automatically sets the subscription to the overloaded method then bubbles.
     *
     * @return  mixed  Results of the event
     */
    public function __call($event, array $args = array())
    {
        $defaults = array(0 => array(), 1 => array());
        $args += $defaults;
        $this->setSubscription($event);
        return $this->bubble($args[0], $args[1]);
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
     * Retrieves the results of the executed event chain.
     *
     * @return  mixed
     */
    public function getChainResults()
    {
        return $this->_chainReturn;
    }
}