<?php
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
 * Subscribes to a prggmr event.
 *
 * @param  string  $event  Name of the event to subscribe
 * @param  closure  $function  Anonymous function to bubble.
 * @param  array  $options  Array of options. Avaliable options.
 *
 *         `shift` - Push this subscriber to the beginning of the queue.
 *
 *         `name` - name to be given to the subscriber; Leave blank to have
 *          a random name given. ( recommended to avoid collisions ).
 *
 *         `force` - force this subscriber if name collision exists.
 *
 *         `namespace` - Namespace for event.
 *         Defaults to \Engine::GLOBAL_DEFAULT.
 *
 * @throws  InvalidArgumentException,RuntimeException
 *
 * @return  void
 */
function subscribe($event, \Closure $function, array $options = array()) {
	return \prggmr\Engine::subscribe($event, $function, $options);
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
 *         `object` - Return the event object.
 *
 *         `suppress` - Suppress exceptions when an event is encountered in
 *         a STATE_ERROR.
 *
 * @throws  LogicException when an error is encountered during subscriber
 *          execution
 *          RuntimeException when attempting to execute an event in
 *          an unexecutable state
 *
 * @return  mixed  Results of event
 * @see  Engine::bubble
 */
function bubble($event, array $params = array(), array $options = array()) {
	return \prggmr\Engine::bubble($event, $params, $options);
}

/**
	* Benchmarks current system runtime useage information for debugging
	* purposes.
	*
    * @param  string  $op  start - Begin benchmark, stop - End Benchmark
	*
	* @param  string  $name  Name of benchmark
	*
	* @return  mixed  Array of info on stop, boolean on start
	*/
function benchmark($op, $name)
{
	return \prggmr\Engine::benchmark($op, $name);
}