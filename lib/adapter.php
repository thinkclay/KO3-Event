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
 * Adapter
 *
 * The default prggmr Engine adapter, this class provides no additional adapter
 * functionality, only providing a bridge to the prggmr Engine.
 */
class Adapter implements AdapterInterface
{

    /**
     * Register a new callable event to the event stack.
     *
     * @param  string  $event  Name of the event to subscribe
     * @param  closure  $function  Anonymous function to bubble.
     * @param  array  $options  Array of options. Avaliable options.
     *
     *         `shift` - Push this subscriber to the beginning of the queue.
     *
     *         `name` - name to be given to the subscriber; Leave blank to have
     *         a random name given. ( recommended to avoid collisions ).
     *
     *         `force` - force this subscriber if name collision exists.
     *
     *         `namespace` - Namespace for event.
     *         Defaults to \Engine::GLOBAL_DEFAULT.
     *
     * @throws  InvalidArgumentException,RuntimeException
     *
     * @return  boolean
     */
    public function subscribe($event, \Closure $function, array $options = array()) {
        return Engine::subscribe($event, $function, $options);
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
    public function bubble($event, array $params = array(), array $options = array()) {
       return Engine::bubble($event, $params, $options);
    }

    /**
     * Checks if a subscriber with the given name currently exists in the
     * subscriber stack.
     *
     * @param  string  $subscriber  Name of the event subscriber.
     * @param  string  $event  Event which the subscriber will execute on.
     * @param  string  $namespace  Namespace subscriber belongs to.
     *         [Default: static::GLOBAL_DEFAULT]
     *
     * @return  boolean  False | True otherwise.
     */
    public function hasSubscriber($subscriber, $event, $namespace) {
        return Engine::hasSubscriber($subscriber, $event, $namespace);
    }

    /**
     * __call directs itself to the Engine bubble.
     *
     * @see  Engine::bubble
     */
    public function __call($event, array $args = array())
    {
        $defaults = array(0 => array(), 1 => array());
        $args += $defaults;
        return $this->bubble($event, $args[0], $args[1]);
    }
}