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
 * The engine object serves as the even processing engine, responsible for
 * interpreting incoming event signals, patching in new event subscribers
 * and firing the event signals.
 */
class Engine {

	/**
     * Array of event subscribers
     *
     * @var  array  Array of anonymous functions waiting for an event.
     */
    protected static $__events = null;

	/**
	 * Debug flag
	 *
	 * @var  boolean  Flag for debugging
	 */
	protected static $__debug = false;

    /**
     * Last Exception handled
     *
     * @var  string  String of the last exception that was handled by debug
     */
    public static $__exception = false;

    /**
     * Provides runtime statistical data.
     *
     * @var  array  Array of runtime statistical information.
     */
    public static $__stats = array(
        'events'     => array(),
        'benchmarks' => array()
    );

	/**
	 * prggmr Default Driver constant.
	 * Default constant is defined for methods that will
	 * use a driver and allow a global default.
	 */
	const GLOBAL_DEFAULT = 'DEFAULT';

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
    public static function subscribe($event, \Closure $function, array $options = array()) {
        $defaults = array('shift' => false,
                          'name' => rand(1, 99999999),
                          'force' => false,
                          'namespace' => static::GLOBAL_DEFAULT);
		$options += $defaults;
		if ($options['name'] instanceof Closure) {
			$fd = false;
			do {
				$name = $options['name']();
				if (!static::hasSubscriber($name, $event, $options['namespace'])) {
					$fd = true;
				}
			} while(!$fd);
			$options['name'] = $name;
		} else {
			if (static::hasSubscriber(
                    $options['name'],
                    $event,
                    $options['namespace']
                ) && false === $options['force']
			) {
				return false;
			}
		}
        $name = $options['name'];
        if (!isset(static::$__events[$options['namespace']])) {
            static::$__events[$options['namespace']] = array();
        }
		if (!isset(static::$__events[$options['namespace']][$event])) {
			static::$__events[$options['namespace']][$event] = array();
		}
		if ($options['shift']) {
			array_unshift_key($options['name'],
				$function, static::$__events[$options['namespace']][$event]);
		} else {
			static::$__events[$options['namespace']][$event][$options['name']] = $function;
		}
        var_dump($function);
        die();
        return true;
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
    public static function hasSubscriber($subscriber, $event, $namespace = null) {
//		if (null === $namespace) $namespace = static::GLOBAL_DEFAULT;
//        if (isset(static::$__events[$namespace][$event][$subscriber])) {
//            return true;
//        }
        return false;
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
     *         `silent` - Suppress exceptions when an event is encountered in
     *         a STATE_ERROR.
     *
     *         `stackResults` - Sets the event results to allow stacking.
     *
     * @throws  LogicException when an error is encountered during subscriber
     *          execution
     *          RuntimeException when attempting to execute an event in
     *          an unexecutable state
     *
     * @return  object  prggmr\Event
     */
    public static function bubble($event, $params = null, array $options = array()) {
        $defaults  = array(
                           'namespace' => static::GLOBAL_DEFAULT,
                           'benchmark' => false,
                           'object' => false,
                           'silent' => false,
						   'stackResults' => null // allow or disallows for engine level stack
                        );
        $options  += $defaults;
        if ($event instanceof Event) {
            // Set the state of this event
            $event->setState(Event::STATE_ACTIVE);
            $eventObj = $event;
            $event = $event->getSubscription();
            $org = $event;
        } else {
            $org   = $event;
            $eventObj = new Event();
            $eventObj->setSubscription($event);
        }
		if (null !== $options['stackResults']) {
			$eventObj->setResultsStackable($options['stackResults']);
		}
        $listeners = null;
        if (!is_array($params)) {
			$params = array();
		}
        if (isset(static::$__events[$options['namespace']][$event])) {
            $listeners = static::$__events[$options['namespace']][$event];
        } else if (isset(static::$__events[$options['namespace']])) {
            foreach (static::$__events[$options['namespace']] as $name => $op) {
				// my_event_:param1_:param
				$regex = $name;
				/**
				 * @todo  Should this be moved to the subscription?
				 */
				$regex = preg_replace('#:([\w]+)#i', '\(?P<$1>[\w_-]+\)', $regex);
				// strange bug.....
				$regex = str_replace('\(', '(', $regex);
				$regex = str_replace('\)', ')', $regex);
                $regex = '#' . $regex . '$#i';
                if (preg_match($regex, $org, $matches)) {
                    $listeners = static::$__events[$options['namespace']][$name];
                    $mc = count($matches);
                    if ($mc != 0) {
                        if ($mc != 1) unset($matches[0]);
						/**
						 * @todo There really has to be a better way
						 */
						foreach ($matches as $_k => $_v) {
							if (is_string($_k)) {
								unset($matches[$_k]);
							}
						}
                        // take the keys from array 1 and merge them ontop of array2
                        foreach ($matches as $k => $v) {
                            $params[] = $v;
                        }
                    }
                }
            }
        }
        array_unshift($params, $eventObj);
        if ($listeners != null) {
            $return = array();
            $i = 0;
            $debug = false;
            foreach ($listeners as $name => $function) {
                if ($debug || static::$__debug && $options['benchmark']) {
                    $debug = true;
                    static::benchmark('start', 'event_'.$event.'_'.$name);
                }
                $halt = $eventObj->isHalted();
                // halt the event bubble
                if ($halt) {
                    break;
                }
                // run the listener
                try {

                    $results = call_user_func_array($function, $params);
                } catch (\Exception $e) {
                    throw new \LogicException(
                        sprintf(
                            'Event (%s) Subscriber "%s" execution failed due to exception "%s" with message "%s"',
							$event,
							$name,
							get_class($e), $e->getMessage()
                        )
                    );
                }
                // Check our event state / halt and print exception unless suppress
                if ($eventObj->getState() === Event::STATE_ERROR) {
					// silently fail...for...might be useful at some point
					if ($options['silent']) return false;
                    throw new \RuntimeException(
                        sprintf(
                            'Error State detected in event (%s) subscriber "%s" with message (%s)',
							$event,
							$name,
							$eventObj->getStateMessage()
                        )
                    );
                }
                if ($debug) {
                    $stats = static::benchmark('stop', 'event_'.$event.'_'.$name);
                    if (!isset(static::$__stats['events'][$event])) {
                        static::$__stats['events'][$event] = array();
                    }
                    static::$__stats['events'][$event][] = array(
                        'stats'   => $stats
                    );
                }
                $i++;
                // Adds support for subscribers to return "false" and halts sequence
                if ($results === false) {
                    break;
                }
                $eventObj->setResults($results);
                if (!$halt && $eventObj->hasChain()) {
                    $chain = $eventObj->executeChain();
                    if ($eventObj->isResultsStackable()) {
                        $eventObj->setResults($chain);
                    }
                }
            }
            if ($options['object'] === true) {
                return $eventObj;
            } else {
                return $eventObj->getResults();
            }
        }
        return true;
    }

	/**
	 * Flushes the engine vars given.
	 *
	 * Options
	 *
	 * [subscribers,s] - full all event subscribers
	 * [registry, r] - flush all registry
	 * [stats] - flush all stats
	 * [exception, e] - flush last exception
	 *
	 *
	 * @return  void
	 */
	public static function flush(/* ... */)
	{
		$vars = func_get_args();
		if (count($vars) == 0) return null;
		foreach ($vars as $_var) {
			switch($_var) {
				case 'subscribers':
				case 's':
					static::$__events = array();
					break;
				case 'registry':
				case 'r':
					static::$__registry = array();
					break;
				case 'stats':
					static::$__stats = array(
						'events'     => array(),
						'benchmarks' => array()
					);
					break;
				case 'exception':
				case 'e':
					static::$__exception = null;
					break;
				default:
					continue;
			}
		}

		return null;
	}

	/**
	 * Returns prggmr's core object registry.
	 *
	 * @param  string  $format  Format in which to return the data [array|object]
	 * 				   defaults to array.
	 *
	 * @return  mixed
	 */
	public static function registry($format = 'array') {
		switch ($format) {
			case 'array':
			default:
				return array(
					'__data'      => static::$__registry,
					'__events'	  => static::$__events,
					'__debug'     => static::$__debug,
                    '__stats'     => static::$__stats
				);
				break;
			case 'object':
				$obj = new \stdClass();
				$array = static::registry('array');
				foreach ($array as $key => $val) {
					$obj->$key = $val;
				}
				return $obj;
				break;
		}
	}

	/**
	 * prggmr Error and Exception Handling.
	 * prggmr handles errors by throwing Exceptions.
	 * Exceptions are handled by outputting the trace and result in an easy to read
	 * format or logged to a specified log file depending on the current debug
	 * mode.
	 *
	 * @param  mixed  $op  Null to return current debug
	 *                Set to true to enable debug mode and false to disable.
	 * 				  Provide an instance of exception the exception handler will
	 * 				  be executed.
	 * 				  Interger and the error handler will be executed.
	 */
	public static function debug($op = null, $string = null, $file = null, $line = null) {
		switch (true) {
            case is_null($op):
            default:
                return static::$__debug;
			case is_bool($op):
                if ($op) {
                    error_reporting(E_ALL ^ E_STRICT);
                    set_error_handler('\prggmr\Engine::debug');
                    set_exception_handler('\prggmr\Engine::debug');
                } else {
                    error_reporting(0);
                }
				static::$__debug = $op;
				break;
			case ($op instanceof Exception):
				if (!defined('LINE_BREAK')) define('LINE_BREAK', "\n");
				$exception   = array();
				$exception[] = sprintf('Exception Thrown [%s]', get_class($op));
				$exception[] = sprintf('Message [%s]', $op->getMessage());
				$exception[] = sprintf('File [%s]', $op->getFile());
				$exception[] = sprintf('Line [%s]', $op->getLine());
				$traceRoute = array_reverse($op->getTrace());
				$num = 0;
				$trace = @array_map(function($v, $i) use (&$num) {
					$file     = (isset($v['file'])) 	? $v['file'] 	 : 'Unknown';
					$line     = (isset($v['line'])) 	? $v['line'] 	 : 0;
					$class    = (isset($v['class'])) 	? $v['class'] 	 : 'Unknown';
					$type 	  = (isset($v['type'])) 	? $v['type'] 	 : '::';
					$function = (isset($v['function'])) ? $v['function'] : 'Unknown';
					$args     = (isset($v['args'])) 	? $v['args'] 	 : null;
					$argString = function() use ($args) {
							if ($args == null) {
								return 'none';
							}
							$return = '';
							foreach ($args as $k => $v) {
								if ($k == 0) {
									$return .= print_r($v, true);
								} else {
									$return .= ', '.print_r($v, true);
								}
							}
							return $return;
						};
					$str = sprintf(
						'{#%d} %s(%d): %s%s%s (%s)',
						$num++,
						$file,
						$line,
						$class,
						$type,
						$function,
						$argString()
						);
					return $str;
				}, $traceRoute);
                static::$__exception = $exception;
                try {
                    static::bubble('exception', array(
									$op,
									$exception), array(
										'namespace' => 'prggmr',
                                        'benchmark' => false
									)
								);
                } catch (LogicException $log) {
                    true;
                }
				if (static::$__debug) {
					echo implode(LINE_BREAK, $exception);
					echo LINE_BREAK."Trace Route".LINE_BREAK.implode(LINE_BREAK, $trace);
				} else {
					// log to a file
				}
				break;
			case is_int($op):
				$exception = new Exception(
					sprintf('%s', $string)
				);
				static::debug($exception);
				break;
		}
		// allways return true to disable php's internal handling
		return true;
	}

    /**
     * Allows for statically overloading event bubbles.
     *
     * @param  mixed  $event  Name of event to bubble
     * @param  array  $params  Array of parameters that will be passed to the
     *         event handler.
     *
     * @return  object  Event
     */
    public static function __callStatic($event, array $params = array()) {
        $defaults = array(0 => null, 1 => array());
        $params += $defaults;
        return static::bubble($event, $params[0], $params[1]);
    }

    /**
     * Returns the current version of prggmr
     *
     * @return  string
     */
    public static function version(/* ... */)
    {
        return PRGGMR_VERSION;
    }
}
