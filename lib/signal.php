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
 * The default signal object allows for signals of any type requiring only
 * that they evalute to true on a strict comparison check, otherwise meaning
 * each signal must be exactly equal both in type and value.
 */
class Signal implements SignalInterface {

    /**
     * The event signal.
     *
     * @var  string
     */
    protected $_signal = null;

    /**
     * Chain signal
     *
     * @var  mixed
     */
    protected $_chain = null;

    /**
     * Constructs a new signal object.
     *
     * @param  mixed  $signal  Event signal
     * @param  mixed  $chain  An additional signal for a chain
     *
     * @return  \prggmr\Queue
     */
    public function __construct($signal, $chain = null)
    {
        if (is_object($signal)) $signal = spl_object_hash($signal);
        $this->_chain = $chain;
        $this->_signal = $signal;
    }

    /**
     * Compares the event signal given with itself.
     *
     * @param  mixed  $signal  Signal to compare
     *
     * @return  mixed  False on failure. True if matches. String/Array
     *          return results found via the match.
     */
    public function compare($signal)
    {
        if (is_object($signal)) $signal = spl_object_hash($signal);
        return ($this->_signal === $signal);
    }

    /**
     * Returns the signal.
     *
     * @return  mixed  Event signal.
     */
    public function signal()
    {
        return $this->_signal;
    }

    /**
     * Returns the signal chain.
     *
     * @return  mixed
     */
    public function getChain()
    {
        return $this->_chain;
    }
}