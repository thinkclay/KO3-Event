<?php
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


use \Exception,
\InvalidArgumentException,
\Closure,
\BadMethodCallException,
\RuntimeException,
prggmr\request\event as request,
prggmr\render\event as render,
prggmr\cli\event as cli,
prggmr\record\connection as connection,
prggmr\util as util,
prggmr\util\data as data;

if (!defined('PRGGMR_LIBRARY_PATH')) {
    define('PRGGMR_LIBRARY_PATH', dirname(__DIR__));
}

define('PRGGMR_VERSION', '0.01-alpha');

require 'prggmr/util/listenable.php';
require 'prggmr/util/event.php';
require 'prggmr/util/singleton.php';
require 'prggmr/util/data/datastatic.php';
require 'prggmr/util/data/datainstance.php';
require 'prggmr/util/functions.php';

class prggmr extends data\DataStatic {

    /**
     * The system routes table. Stores the routes which will be
     * transversed to locate the current route.
     *
     * @var  array  Table of routes.
     */
    protected static $__routes = array();


    /**
     * prggmr registry property, information is stored as a `key` -> `value` pair.
     *
     * @var  array  Array of `key` -> `value` mappings for registry contents.
     */
    protected static $__registry = array();

    /**
     * List of libraries from which classes are loaded.
     * Libraries can be added via `prggmr::library()`
     *
     * @var  array  Avaliable libraries for loading classes and files.
     */
    protected static $__libraries = array();

	/**
     * Array of event listeners
     *
     * @var  array  Array of anonymous functions waiting for an event.
     */
    protected static $__events = array();

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
	const GLOBAL_DEFAULT = 'prggmr';

    /**
     * Loads a PHP file defined by `$class`. Looks through the list of
     * paths set at `static::$__libraries`.
     * Paths can be added by calling `\prggmr::library()`.
     * Loading is also possible by providing the exact path in the form of a
     * decimal delimited string. e.g.. `prggmr::load('library.mylib.orm.sqlite')`
     *
     * @param  string  $class  Either a fully-namespaced classname or a decimal
     *         string containing the full path to a specific file.
     *
     * @param  boolean  $require  Require file. Exception thrown if not found
     *
     * @param  array   $options  Array of options. Avaliable options
     *
     *         `return_path` - Returns the full generated path to the file.
     *         If mulitple loaders are run an array if returned contained all
     *         generated paths.
     *
     *         `require` - Set this file to be required. If not found a
     *         Runtime exception is thrown.
     *
     * @throws  RuntimeException  Thrown when file cannot be located and
     *          require is true
     * @return  boolean  True on success, otherwise an exception is thrown
     */
    public static function load($class, array $options = array()) {
        $defaults = array('return_path' => false, 'require' => false);
        $options += $defaults;
        $namespace = null;
        $class = ltrim($class, '\\');
        if (strpos($class, '.') !== false) {
            $class     = implode('/', explode('.', $class));
        } else {
            if ($nspos = strripos($class, '\\')) {
                $namespace = substr($class, 0, $nspos);
                $class     = substr($class, $nspos + 1);
            }
        }

        $paths = array();

        $pathCheck = function($path, $config) use (&$namespace, &$class, &$options, &$pathCheck) {

            $return = array();

            if ($config['transformer'] instanceof \Closure) {
                $location = $config['transformer']($class, $namespace, $options);
                // Add allowance of skipping this library if dictated
                if ($location === null || $location === false) {} else {
                    $fullPath = $config['prefix'].$path.$location.$config['ext'];
                }
            } else {
                $fullPath = $config['prefix'].$path.$namespace.$class.$config['ext'];
            }

            return $fullPath;
        };

        foreach (static::$__libraries as $name => $config) {
            if (is_array($config['path'])) {
                foreach ($config['path'] as $k => $_path) {
                    $checkPaths[] = $pathCheck($_path, $config);
                }
            } else {
                $checkPaths = array($pathCheck($config['path'], $config));
            }
            //$checkPaths = $pathCheck($config['path'], $config);
            $paths += $checkPaths;
            foreach ($checkPaths as $k => $path) {
                if (!$options['return_path']) {
                    if (file_exists($path)) {
                        include $path;
                        return true;
                    }
                }
            }
        }

        if ($options['return_path']) {
            return $paths;
        }

        if ($options['require']) {
            throw new \RuntimeException(
                sprintf(
                    'Failed to load file "%s"; Libraries scanned "%s"', $class, implode(',', $paths)
                )
            );
        } else {
            return false;
        }

    }

    /**
     * Adds a library for which a file can be found via `prggmr::load()`.
     *
     * @param  string  $name  Name of the library e.g. `prggmr`, `Zend`, `Pear`
     * @param  array  $options  Array of options that allow manipulation of the
     *         loader, and library specific definitions on how to load files
     *         within the library. Avaliable Types:
     *
     *         `path` - Path to this library. Leave blank to use the current
     *         PRGGMR_LIBRARY_PATH.
     *
     *         `prefix` - String prepended to the file path string. e.g..
     *         `library/myclasses` -> `library/myclasses/mylib/orm/sqlite`
     *
     *
     *         `ext` - String extension appended to file paths e.g.. .inc.php
     *         .class.php, .php5, default = .php
     *
     *         `transformer` - A Closure that accepts the `$class`, `$namespace`
     *         and `$options` as the parameters and returns a modified class name.
     *
     *         `shift` - Prepend route to the beginning of the routes table if set to true.
     *
     *         `merge` - Merges a current library loader with new configuration, allowing
     *         modification of library loading at runtime.
     *
     * @throws  InvalidArgumentException  Thrown when directory path cannot be found
     *
     * @return  boolean  Returns true when library is succesfully added.
     */
    public static function library($name, array $options = array()) {
        $defaults = array(
            'path'        => PRGGMR_LIBRARY_PATH,
            'prefix'      => null,
            'ext'         => '.php',
            'transformer' => function($class, $namespace, $options) {
                $namespace = ($namespace == null) ? '' : str_replace('\\', DIRECTORY_SEPARATOR, $namespace).DIRECTORY_SEPARATOR;
                $class = str_replace('_', DIRECTORY_SEPARATOR, $class);
                return $namespace.$class;
            },
            'shift'       => false,
			'update'      => false,
            'merge'       => false
        );

        $merge = false;

        if (isset($options['merge']) && true == $options['merge']) {
            $merge = true;
        }

        if (isset(static::$__libraries[$name]) && $merge) {
            $tmp = array_merge_recursive(static::$__libraries[$name], $options);
            $options = $tmp;
        } else {
            $options += $defaults;
        }

		$pathcheck = function($path) {
			// PATH_SEPERATOR
			if (strpos($path, PATH_SEPARATOR) !== false) {
				$path = explode(PATH_SEPARATOR, $path);
			} else {
				$path = array($path);
			}
			foreach ($path as $v) {
				if (!is_dir($v)) {
					throw new \InvalidArgumentException(
						sprintf(
							'Library path "%s" is not a valid path', $v
						)
					);
				}
			}
		};

		if (is_array($options['path'])) {
			array_map($pathcheck, $options['path']);
		} else {
			$pathcheck($options['path']);
		}

        if ($merge && array_key_exists($name, static::$__libraries)) {
            if (!is_array(static::$__libraries[$name]['path'])) {
                static::$__libraries[$name]['path'] = array(static::$__libraries[$name]['path']);
            }
            static::$__libraries[$name]['path'] += (array) $options['path'];
            return true;
        }

        if ($options['shift'] === true) {
            array_unshift_key($name, $options, static::$__libraries);
        } else {
            static::$__libraries[$name] = $options;
        }
        return true;
    }

    /**
     * Router operations are performed as anonymous functions tied to an array
     * set at `static::$__routes`.
     * The router operates by providing either a system route or a router operation
     * as the first parameter. Routes are regular expressions supporting named
     * subpatterns which are matched in a chain, triggering the action that is tied
     * to a route, which are anonymous functions that must always return `true`
     * or a RuntimeException will be thrown.
     * """
     * example
     * Trigger a route to `blog/test-post` and call the Blog()->view() method.
     * prggmr::router('^blog/(?P<slug>[a-zA-Z-0-9]+)/$', function($slug) { return Blog::view($slug); });
     * """
     *
     * @param  string  $op  A system route or a router operation. Avaliable operations.
     *
     *          `regex` - A regular expression string to match.
     *
     *          `dispatch` - Dispatch the system router.
     *
     *          `routes` - Return the current system routes table.
     *
     * @param  object  $arg  The operation that will be performed if the `regex`
     *         is matched, the anonymous function that returns the string to match for the route,
     *         or the string to use for matching the routes.
     *
     * @param  array  $options  Array of options to use for this route. Avaliable options
     *
     *         `shift` - Prepend route to the beginning of the routes table if set to true.
     *
     *         `force` - Overwrites previous route is same exists if set to true.
     *
     *         `params` - Set of additional parameters to provide the route action.
     *         See [[http://www.nwhiting.com]] for examples.
     *
     *         `flags`  - Flags to pass to the `preg_match` function used for matching the
     *         routes table.
     *
     *         `offset` - Specify the alternate place from which to start the search.
     *
     *
     * @throws  LogicException, RuntimeException, InvalidArgumentException
     * @return  boolean
     */
    public static function router($op, $arg = null, array $options = array()) {
        $defaults = array('shift' => false, 'force' => false, 'flags' => null, 'offset' => null, 'params' => array());
        $options += $defaults;
        switch ($op) {
            case 'routes':
                return static::$__routes;
                break;
            case 'dispatch':
                if ($arg !== null) {
                    if ($arg instanceof Closure) {
                        $arg = $arg();
                    } elseif (!is_string($arg)) {
                        throw new \InvalidArgumentException(
                            sprintf(
                                'Dispatch expected string or closure for route lookup; received "%s"', gettype($arg)
                            )
                        );
                    }
                } else {
                    $arg = $_SERVER['REQUEST_URI'];
                }
                static::set('prggmr.router.uri', $arg);
                /**
                * Event based system
                */
                $event = static::trigger('router.dispatch.startup', array('uri' => $arg));
                if (count($event) != 0) {
                    (array) $options['params'] += $event;
                }
                try {
                    $operation = array_walk(static::$__routes, function($action, $route) use ($arg, $options) {
                        $route = '#' . $route . '$#i';
                        if (preg_match($route, $arg, $matches, $options['flags'], $options['offset'])) {
                            unset($matches[0]);
                            extract($action, EXTR_OVERWRITE);
                            extract($options, EXTR_OVERWRITE);
                            if (count($params) != 0) {
                                $matches += $params;
                            }
                            $array = array();
                            array_walk($matches, function($value, $key) use (&$array) {
                                if (is_int($key)) {
                                    $array[$key] = $value;
                                }
                            });
                            return call_user_func_array($function, $array);
                        }
                    });
                } catch (Exception $e) {
                    throw new \LogicException(
                        sprintf(
                            'Dispatch execution failed due to exception "%s" with message "%s"', get_class($e), $e->getMessage()
                        )
                    );
                }
                break;
            default:
                if (!is_object($arg)) {
                    throw new \InvalidArgumentException(
                        sprintf(
                            'prggmr route "%s" action is invalid; expected object received "%s"', gettype($arg)
                        )
                    );
                }
                if (!$arg instanceof Closure) {
                    throw new \InvalidArgumentException(
                        sprintf(
                            'prggmr route "%s" action is invalid; expected closure received "%s"', get_class($arg)
                        )
                    );
                }
                if (isset(static::$__routes[$op]) && $options['force'] !== true) {
                    throw new \RuntimeException(
                        sprintf(
                            'prggmr route "%s" already exists; Provide "force" option to overwrite', $regex
                        )
                    );
                }
                $arg = array('function' => $arg, 'params' => $options['params']);
                if ($options['shift'] === true) {
                    array_unshift_key($op, $arg, static::$__routes);
                } else {
                    static::$__routes[$op] = $arg;
                }
                break;
        }
        return true;
    }

	/**
     * Adds a new listener to the event queue.
     * Listeners are anonymous functions that are executed via a triggered
     * event in which they are listening for.
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
    public static function listen($event, \Closure $function, array $options = array()) {
        $defaults = array('shift' => false,
                          'name' => str_random(12),
                          'force' => false,
                          'namespace' => static::GLOBAL_DEFAULT);
		$options += $defaults;
        $event = strtolower($event);
		if ($options['name'] instanceof Closure) {
			$fd = false;
			do {
				$name = $options['name']();
				if (!static::hasListener($event, $name, $options['namespace'])) {
					$fd = true;
				}
			} while(!$fd);
			$options['name'] = $name;
		} else {
			if (static::hasListener($event, $options['name'], $options['namespace']) && !$options['force']) {
				throw new \RuntimeException(
					sprintf(
						'prggmr listener "%s" already exists; Provide "force" option to overwrite', $options['name']
					)
				);
			}
		}
        if (!isset(static::$__events[$options['namespace']])) {
            static::$__events[$options['namespace']] = array();
        }
		if (!isset(static::$__events[$options['namespace']][$event])) {
			static::$__events[$options['namespace']][$event] = array();
		}
		if ($options['shift']) {
			array_unshift_key($options['name'], $function, static::$__events[$options['namespace']][$event]);
		} else {
			static::$__events[$options['namespace']][$event][$options['name']] = $function;
		}

        return true;
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
    public static function hasListener($listener, $event, $namespace) {
        if (isset(static::$__events[$namespace][$event][$listener])) {
            return true;
        }
        return false;
    }

    /**
     * Triggers event listeners and returns the results; the results are
     * always returned in an array for use with events with multiple
     * listeners returning results.
     *
     * @param  string  $event  Name of the event to trigger
     * @param  array  $params  Parameters to directly pass to the event listener
     * @param  array  $options  Array of options. Avaliable options.

     *         `namespace` - `namespace` - Namespace for event.
     *         Defaults to \prggmr::GLOBAL_DEFAULT.
     *
     *         `benchmark` - Benchmark this events execution.
     *
     *         `flags`  - Flags to pass to the `preg_match` function used for matching a
     *         regex event.
     *
     *         `offset` - Specify the alternate place from which to start the search.
     *
     *         `object` - Return the event object.
     *
     *         `suppress` - Suppress exceptions when an event is encountered in a STATE_ERROR.
     *
     * @throws  LogicException when an error is encountered during listener
     *          execution
     *          RuntimeException when attempting to execute an event in
     *          an unexecutable state
     *
     * @return  object  prggmr\util\Event
     */
    public static function trigger($event, array $params = array(), array $options = array()) {
        $defaults  = array(
                           'namespace' => static::GLOBAL_DEFAULT,
                           'benchmark' => false,
                           'flags' => null,
                           'offset' => null,
                           'object' => false,
                           'suppress' => false
                        );
        $options  += $defaults;
        if ($event instanceof util\Event) {
            // Check the state of this event
            $event->setState(util\Event::STATE_ACTIVE);
            $eventObj = $event;
            $event = $event->getListener();
            $org = $event;
        } else {
            $org   = $event;
            $event = strtolower($event);
            $eventObj = new util\Event($event);
        }
        $evreg     = '#' . $event . '$#i';
        $listeners = null;
        if (!is_array($params)) $params = array();
        if (isset(static::$__events[$options['namespace']][$event])) {
            $listeners = static::$__events[$options['namespace']][$event];
        } else if (isset(static::$__events[$options['namespace']])) {
            foreach (static::$__events[$options['namespace']] as $name => $op) {
                $regex = '#' . $name . '$#i';
                if (preg_match($regex, $org, $matches, $options['flags'], $options['offset'])) {
                    $listeners = static::$__events[$options['namespace']][$name];
                    $mc = count($matches);
                    if ($mc != 0) {
                        if ($mc != 1) unset($matches[0]);
                        // take the keys from array 1 and merge them ontop of array2
                        foreach ($matches as $k => $v) {
                            array_unshift($params, $v);
                        }
                    }
                    #$params = $matches;
                    #break;
                }
            }
        }
        array_unshift($params, &$eventObj);
        if ($listeners != null) {
            $return = array();
            $i = 0;
            $debug = false;
            foreach ($listeners as $name => $function) {
                if ($debug || static::$__debug && $options['benchmark']) {
                    $debug = true;
                    static::analyze('bench_begin', array('name' => 'event_'.$name.'_'.$i));
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
                            'Event (%s) Listener "%s" execution failed due to exception "%s" with message "%s"', $event, $name, get_class($e), $e->getMessage()
                        )
                    );
                }
                // Check our event state / halt and print exception unless suppress
                if ($eventObj->getState() === util\Event::STATE_ERROR && !$options['suppress']) {
                    throw new \RuntimeException(
                        sprintf(
                            'Error State detected in event (%s) listener "%s" with message (%s)', $event, $name, $eventObj->getStateMessage()
                        )
                    );
                }
                if ($debug) {
                    $stats = static::analyze('bench_stop', array('name' => 'event_'.$name.'_'.$i));
                    if (!isset(static::$__stats['events'][$name])) {
                        static::$__stats['events'][$name] = array();
                    }
                    static::$__stats['events'][$name][] = array(
                        'results' => $return[$i],
                        'stats'   => $stats
                    );
                }
                $i++;
                // Adds support for listeners to return "false" and halts sequence
                if ($results === false) {
                    break;
                }
                $eventObj->setResults($results);
                if (!$halt && $eventObj->hasChain()) {
                    $chain = $eventObj->triggerChain();
                    if ($eventObj->isResultsStackable()) {
                        $eventObj->setResults($chain);
                    }
                }
            }
            if ($options['object']) {
                return $eventObj;
            } else {
                return $eventObj->getResults();
            }
        }
        return true;
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
					'__routes'    => static::$__routes,
					'__data'      => static::$__registry,
					'__libraries' => static::$__libraries,
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
                    set_error_handler('\prggmr::debug');
                    set_exception_handler('\prggmr::debug');
                } else {
                    error_reporting(0);
                }
				static::$__debug = $op;
				break;
			case ($op instanceof Exception):
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
						'{#%d} %s(%d): %s%s%s (%s)', $num++, $file, $line, $class, $type, $function, $argString()
						);
					return $str;
				}, $traceRoute);
                static::$__exception = $exception;
                try {
                    static::trigger('exception', array($op, $exception), array('namespace' => 'prggmr',
                                                                               'benchmark' => false));
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
     * Analyzes current system runtime useage information for debugging
     * purposes.
     *
     * @param  string  $op  Operation to perform.
     *
     *         `cpu' - Returns the current CPU Usuage.
     *         Not Implement looking for reliable method. Suggestions?
     *
     *         `memory` - Returns the current memory usuage.
     *
     *         `bench_begin` - Begins a benchmark which captures CPU, Memory
     *         and execution time. Used inconjunction with `bench_start`
     *
     *         `bench_stop` - Finalizes a benchmark.
     *         Used in conjunction with `bench_start`
     *
     * @param  array  $options  Array of options. Avaliable options.
     *
     *         `name` - Name of benchmark. Required Parameter
     */
    public static function analyze($op, array $options = array())
    {
        if (false == PRGGMR_DEBUG) {
            return true;
        }

        $defaults = array();
        $options += $defaults;
        $microtime = function() {
            $time = explode(" ",microtime());
            return $time[0] + $time[1];
        };

        $memory = function() {
            if (function_exists('memory_get_usage')) {
                return memory_get_usage();
            } else {
                return 0;
            }
        };

        switch ($op) {
            #case 'cpu':
            #    return $cpu();
            #    break;
            case 'memory':
                return $memory();
                break;
            case 'bench_begin':
                if (!isset($options['name'])) {
                    throw new \InvalidArgumentException(
                        'Invalid arguments recieved. Expected option `name`'
                    );
                }
                $stats = array(
                    'memory' => $memory(),
                    'time'   => $microtime()
                    #'cpu'    => $cpu(),
                );
                static::set('prggmr.stats.benchmark.'.$options['name'], $stats);
                static::trigger('benchmark_begin',$stats,array(
                                                         'namespace' => 'prggmr',
                                                         'benchmark' => false
                                                         ));
                break;
            case 'bench_stop':
                if (!isset($options['name'])) {
                    throw new \InvalidArgumentException(
                        'Invalid arguments recieved. Expected option `name`'
                    );
                }
                $data = array(
                              #'cpu'    => $cpu(),
                              'memory' => $memory(),
                              'time'   => $microtime(),
                              'start'  => 0,
                              'end'    => time()
                              );
                $stats = static::get('prggmr.stats.benchmark.'.$options['name']);
                if ($stats != false) {
                    $data['memory'] = ($stats['memory'] > $data['memory']) ? $stats['memory'] - $data['memory'] : $data['memory'] - $stats['memory'];
                    #$data['cpu'] = ($start['cpu'] > $data['cpu']) ? $stats['cpu'] - $data['cpu'] : $data['cpu'] - $stats['cpu'];
                    $data['time'] = $data['time'] - $stats['time'];
                    $data['start'] = $stats;
                }
                static::trigger('benchmark_stop', $data, array(
                                                         'namespace' => 'prggmr',
                                                         'benchmark' => false
                                                         ));
                static::set('prggmr.stats.benchmark.'.$options['name'], $data);
                static::$__stats['benchmarks'][$options['name']] = $data;
                return $data;
                break;
        }
    }

    /**
     * Triggers event listeners and returns the results; the results are
     * always returned in an array for use with events with multiple
     * listeners returning results.
     *
     * @param  string  $event  Name of the event to trigger
     * @param  array  $params  Parameters to directly pass to the event listener
     * @param  array  $options  Array of options. Avaliable options.

     *         `namespace` - `namespace` - Namespace for event.
     *         Defaults to \prggmr::GLOBAL_DEFAULT.
     *
     *         `benchmark` - Benchmark this events execution.
     *
     *         `flags`  - Flags to pass to the `preg_match` function used for matching a
     *         regex event.
     *
     *         `offset` - Specify the alternate place from which to start the search.
     *
     * @throws  LogicException  Exception encountered during listener exec
     * @return  array|boolean
     */
    public static function __callStatic($event, array $params = array()) {
        $defaults = array(0 => array(), 1 => array());
        $params += $defaults;
        return static::trigger($event, $params[0], $params[1]);
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

	/**
	 * Initials prggmr framework, loads the configuration file, establishes
	 * our default library paths, includes our functions file.
	 *
	 * @param  string  $config  Path to configuration file.
	 */
	public static function initalize($config = null)
	{
		if (null === $config) {
			throw new InvalidArgumentException(
				'No configuration file provided. Please provide a prggmr config file path'
			);
		}

		$config = \parse_ini_file($config, true);

		if (!is_array($config) || count($config) == 0) {
			throw new InvalidArgumentException(
				sprintf(
						'Invalid configuration file (%s)',
						$config
				)
			);
		}

        // Set our configuration values
		static::set('prggmr.config', $config);

        if (!defined('PRGGMR_DEBUG')) {
            define('PRGGMR_DEBUG', $config['system']['debug']);
        }

		static::analyze('bench_begin', array('name' => 'prggmr benchmark'));


		// Setup our system paths
		// Library Files
		static::library('prggmr', array(
			'path'   => $config['paths']['system_path'].'/lib/',
			'prefix' => null,
			'ext'    => '.php',
			'transformer' => function($class, $namespace, $options) {
				$namespace = ($namespace == null) ? '' : str_replace('\\', DIRECTORY_SEPARATOR, $namespace).DIRECTORY_SEPARATOR;
				$class = str_replace('_', DIRECTORY_SEPARATOR, $class);
				$filepath = strtolower($namespace.$class);
				return $filepath;
			}
		));

		// External Library files ( Uses PECL style formatting )
		static::library('prggmr.external', array(
			'path' => $config['paths']['system_path'].'/lib/'
		));

		// Setup our system library in php
		spl_autoload_register('\prggmr::load');

		// Listen for prggmr's dispatcher
		static::listen('router.dispatch.startup', function($uri) {
			$front = new request\Dispatch(new render\Output);
			$front->attach(array('uri'=>$uri));
			$front->dispatch();
			return $front;
		});

		//if (static::get('prggmr.config.system.debug')) {
		//	$cli = new cli\Handler($_SERVER['argv']);
		//	static::router('dispatch', $cli->run());
		//}
	}
}