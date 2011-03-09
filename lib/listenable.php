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


/**
 * Listenable
 *
 * The listenable class provides an abstraction layer for registering
 * objects as a simple publishing/subscriber interface to the event engine.
 */
class Listenable extends util\DataInstance
{
    /**
     * Stack of event listeners listening to this object.
     *
     * @var  array  Stack of event listeners.
     */
    protected $_listeners = array();

    /**
     * Register a new callable event to the event stack.
     *
     * @param  string  $event  Name of the event to trigger the listener
     * @param  closure  $function  Anonymous function to execute when the
     *         trigger is executed.
     * @param  array  $options  Array of options. Avaliable options.
     *
     *         `shift` - Push this listener to the beginning of the queue.
     *
     *         `name` - name to be given to the listener; Leave blank to have
     *         a random name given. ( recommended to avoid collisions ).
     *
     *         `force` - force this listener is name collision exists.
     *
     *         `namespace` - Namespace for event.
     *         Defaults to \prggmr::GLOBAL_DEFAULT.
     *
     * @throws  InvalidArgumentException,RuntimeException
     * @return  boolean
     *
     */
    public function listen($event, \Closure $function, array $options = array()) {
        $defaults = array('shift' => false,
                          'name' => str_random(12)
                         );
        $options += $defaults;
        // ok ... we have some duplicate code here ....
        // make another method just for this?
        // or add into an event object?
        if ($options['name'] instanceof Closure) {
			$fd = false;
			do {
				$name = $options['name']();
				if (!\prggmr::hasListener($event, $name, $options['namespace'])) {
					$fd = true;
				}
			} while(!$fd);
			$options['name'] = $name;
		} else {
			if (\prggmr::hasListener($event,
                                    $options['name'],
                                    $options['namespace']) && !$options['force']) {
				throw new \RuntimeException(
					sprintf(
						'prggmr listener "%s" already exists;
                        Provide "force" option to overwrite',
                        $options['name']
					)
				);
			}
		}
        if (!isset($this->_listeners[$event])) {
			$this->_listeners[$event] = array();
		}
		if ($options['shift']) {
			array_unshift_key($options['name'], $function, $this->_listeners[$event]);
		} else {
			$this->_listeners[$event][$options['name']] = $function;
		}
        return \prggmr::listen($event, $function, $options);
    }

    /**
     * Triggers an event within the current scope.
     *
     * @param  array  $params  Parameters to directly pass to the event listener
     * @param  array  $options  Array of options. Avaliable options
     *
     *         `namespace` - `namespace` - Namespace for event.
     *         Defaults to \prggmr::GLOBAL_DEFAULT.
     *
     *         `benchmark` - Benchmark this events execution.
     *
     *         `flags`  - Flags to pass to the `preg_match` function used for
     *         matching a regex event.
     *
     *         `offset` - Specify the alternate place from which to start the search.
     *
     *         `object` - Return the event object.
     *
     *         `suppress` - Suppress exceptions when an event is encountered in
     *         a STATE_ERROR.
     *
     * @throws  LogicException when an error is encountered during listener
     *          execution
     *          RuntimeException when attempting to execute an event in
     *          an unexecutable state
     *
     * @return  object  prggmr\util\Event
     * @see  prggmr::trigger
     */
    public function trigger($event, array $params = array(), array $options = array()) {

        if (!$event instanceof Event) {
            $name = $event;
            $event = new Event($name);
        }

        $defaults  = array('errors' => false);
        $options += $defaults;
        $results = \prggmr::trigger($event, $params, $options);
        if ($options['errors']) {
            foreach ($results as $_listener => $_results) {
                if (false === $_results) {
                    throw new \RuntimeException(
                        sprintf(
                            'Event listener %s" was recorded as a
                            failure within event "%s"',
                            $_listener,
                            $_event
                        )
                    );
                }
            }
        }
        return $results;
    }

    /**
     * Checks if a listener with the given name currently exists in the
     * listeners stack.
     *
     * @param  string  $listener  Name of the event listener.
     * @param  string  $event  Event which the listener will execute on.
     * @param  string  $namespace  Namespace listener belongs to.
     *         [Default: static::GLOBAL_DEFAULT]
     *
     * @return  boolean  False if non-existant | True otherwise.
     */
    public function hasListener($listener, $event, $namespace) {
        if (isset($this->_events[$event][$listener])) {
            return true;
        }
        return false;
    }

    /**
     * Returns the array stack of current event listeners attached
     * to this object.
     *
     * @return  array  Stack of event listeners.
     */
    public function getListeners(/* ... */)
    {
        return $this->_listeners;
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
        return $this->trigger($event, $args[0], $args[1]);
    }
}