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
 * Event object.
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
     * Data attatched to this event.
     *
     * @var  object  \prggmr\util\data\DataInstance
     */
    protected $_data = null;

    /**
     * Results results of any event listeners attatched to this event.
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
     * Internal name of this event.
     *
     * @var  string  Name of the event.
     */
    protected $_name = null;

    /**
     * Listener in which this event will fire upon.
     *
     * @var  string  Name of event listener.
     */

    /**
     * Constructs a new event object.
     */
    public function __construct($name, array $data = null, Event $parent = null)
    {
        $this->_data = new data\DataInstance();

        if (null !== $data) {
            $this->_data->set($data);
        }

        if (null !== $parent) {
            $this->_parent = $parent;
        }
    }

    /**
     * Sets the event state.
     *
     * @param  integer  $state  State this event is currently in.
     *
     * @throws  InvalidArgumentException when invalid state is given.
     *
     * @return  boolean  True on success
     */
    public function setState(integer $state)
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
        return true;
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
     * @return  null
     */
    public function haltSequence()
    {
        $this->_halt = true;
    }

    /**
     * Returns the flag to halt the event stack once the
     * event completes execution.
     *
     * @return  boolean  True to halt | False otherwise
     */
    public function isHalted()
    {
        return $this->_halt;
    }
}