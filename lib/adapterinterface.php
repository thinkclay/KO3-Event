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
 * Adapter Interface defines the methods for a Prggmr Engine adapter.
 */
interface AdapterInterface
{
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
    public function subscribe($signal, $subscription, $identifier = null, $priority = null);

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
    public function fire($signal, array $vars = array(), $event = null);
}