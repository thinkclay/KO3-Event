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

use
\Exception,
\InvalidArgumentException,
\Closure,
\BadMethodCallException,
\RuntimeException;

if (!defined('PRGGMR_LIBRARY_PATH')) {
    define('PRGGMR_LIBRARY_PATH', dirname(__DIR__));
}

define('PRGGMR_VERSION', '0.1.1');

/**
 * PHP 5.3+
 * Shifts the value onto the begginning of the array
 * with the key index provided.
 *
 * @param  string  $key  Key value of array index
 * @param  mixed  $value  Value of new array index
 * @param  array  $array  Array to shift new element
 *
 * @return  array  Newly formed array
 */
function array_unshift_key($key, $value, array &$array) {
    $key = (string) $key;
    if (!is_array($array)){
        return false;
    }
    $tmp = array($key => $value);
    $tmp += $array;
    $array = $tmp;
    return $array;
}

/**
 * Returns the name of a class using get_class with the namespace stripped.
 * This will not work inside a class scope as get_class() a workaround for
 * that is using get_class_name(get_class());
 *
 * @param  object|string  $object  Object or Class Name to retrieve name

 * @return  string  Name of class with namespaces stripped
 */
function get_class_name($object = null)
{
    if (!is_object($object) && !is_string($object)) {
        return false;
    }

    $class = explode('\\', (is_string($object) ? $object : get_class($object)));
    return $class[count($class) - 1];
}

/**
 * Returns a random string of alphabetical characters.
 *
 * @param  integer  $length  Length of the string.
 *
 * @return  string  String of random alphabetical characters.
 */
function str_random($length = 8) {
    $range = range('a','z');
    $rand = array_rand($range, $length);
    $str = '';
    for ($i=0;$i!=$length;$i++){
        $str .= $range[$rand[$i]];
    }
    return $str;
}

/**
 * Static Data Class
 *
 * This class allows for the manipulation and handling array data using
 * "." delimited strings rather than the method of
 * $array['parent']['child']['sibiling']['etc']['etc'].
 */
class DataStatic
{
    /**
     * Registry property, information is stored as a `key` -> `value` pair.
     *
     * @var  array  Array of `key` -> `value` mappings for registry contents.
     */
    protected static $__registry = array();

    /**
     *  Sets a variable within the prggmr registry.
     *  Variables can be set using three different configurations, they can be
     *  set as an ordinary `$key`, `$value` pair, an array of `$key` => `$value`
     *  mappings which will be transversed, or finally as a "." delimited string
     *  in the format of `$key`, `$value` which will be transformed into an array,
     *  these configurations can also be combined.
     *
     *  @param  mixed  $key  A string identifier, array of key -> value mappings,
     *          or a "." delimited string.
     *  @param  mixed  $value  Value of the `$key`.
     *  @param  boolean  $overwrite  Overwrite existing key if exists
     *
     *  @return  boolean
     */
    public static function set($key, $value = null, $overwrite = true) {

		if (null === $key) {
			return false;
		}

        if (is_array($key)) {
            foreach ($key as $k => $v) {
                static::set($k, $v, $overwrite);
            }
            return true;
        }

        if (true === static::has($key) && !$overwrite) {
            return false;
        }
		
        if (false !== strpos($key, '.')) {
			$nodes  = explode('.', $key);
			$data =& static::$__registry;
			$nodeCount = count($nodes) - 1;
			for ($i=0;$i!=$nodeCount;$i++) {
                // Bug caused data to not overwrite if converting from ( any ) -> array
                // and an overwrite is in order
				if (!is_array($data[$nodes[$i]])) {
					$data[$nodes[$i]] = array();
				}
				$data =& $data[$nodes[$i]];
			}
			$data[$nodes[$nodeCount]] = $value;
			return true;
		} else {
            static::$__registry[$key] = $value;
        }

        return true;
    }

    /**
     * Returns a variable from `engine::$__registry`. The variable name can be
     * provided as a single string of the variable or a "." delimited string
     * which maps to the array tree storing this variable.
     *
     * @param  string  $key  A string of the variable name or a "." delimited
     *         string containing the route of the array tree.
     * @param  array   $options  An array of options to use while retrieving a
     *         variable from the cache. Avaliable options.
     *
     *         `default` - Default value to return if `$key` is not found.
     *
     *         `tree` - Not Implemented
     *
     * @return  mixed
     */
    public static function get($key, $options = array()) {
        $defaults = array('default' => false, 'tree' => true);
        $options += $defaults;
        if (is_string($key)) {
            if (false !== strpos($key, '.')) {
                $keyArray = explode('.', $key);
                $count    = count($keyArray) - 1;
                $last     = $keyArray[$count];
                $data     = static::$__registry;
                for ($i=0;$i!=count($keyArray);$i++) {
                    $node = $keyArray[$i];
                    if ($node !== '') {
                        if (array_key_exists($node, $data)) {
                            if ($node == $last && $i == $count) {
                                return $data[$node];
                            }
                            if (is_array($data[$node])) {
                                $data = $data[$node];
                            }
                        }
                    }
                }
                if ($data !== static::$__registry) {
                    return $data;
                }
            }

            return (!isset(static::$__registry[$key])) ?
					$options['default'] :
					static::$__registry[$key];
        }

        throw new \InvalidArgumentException(
            sprintf(
                'Invalid arugment "$key" expected "string" received "%s"',
				gettype($key)
            )
        );
    }

    /**
     * Returns if a variable identified by `$key` exists in the registry.
     * `has` works just as `get` and allows for identical `$key`
     * configuration.
     * This is a mirrored shorthand of
     * (engine::get($key, array('default' => false)) !== false);
     *
     * @param  $key  A string of the variable name or a "." delimited
     *         string containing the route of the array tree.
     * @return  boolean
     */
    public static function has($key) {
        return (static::get($key, array('default' => false)) !== false);
    }
}

/**
 * Engine
 *
 * Prggmr in short is a EDA wrapped into a solution in PHP.
 * Prggmr uses a simple event processing as its current processing que
 * while a complex event processing system is being planned, this is not
 * to say the engine is not powerful.
 *
 * The prggmr object works as the event engine, system debugger, php autoloader
 * and a performance benchmarking tool.
 *
 */
class engine extends DataStatic {

    /**
     * List of libraries from which classes are loaded.
     * Libraries can be added via `engine::library()`
     *
     * @var  array  Avaliable libraries for loading classes and files.
     */
    protected static $__libraries = array();

	/**
     * Array of event subscriber
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
	const GLOBAL_DEFAULT = 'DEFAULT';

    /**
     * Loads a PHP file defined by `$class`. Looks through the list of
     * paths set at `static::$__libraries`.
     * Paths can be added by calling `engine::library()`.
     * Loading is also possible by providing the exact path in the form of a
     * decimal delimited string. e.g.. `engine::load('library.mylib.orm.sqlite')`
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

        $pathCheck = function($path, $config) use (
						&$namespace, &$class, &$options, &$pathCheck) {

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
                    'Failed to load file "%s"; Libraries scanned "%s"',
					$class,
					implode(',', $paths)
                )
            );
        } else {
            return false;
        }

    }

    /**
     * Adds a library for which a file can be found via `engine::load()`.
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
     *         and `$options` as the parameters and returns a modified
     *         class name.
     *
     *         `shift` - Prepend route to the beginning of the routes table
     *         if set to true.
     *
     *         `merge` - Merges a current library loader with new configuration,
     *         allowing modification of library loading at runtime.
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
                $namespace = ($namespace == null)
				? '' :
				str_replace('\\', DIRECTORY_SEPARATOR, $namespace).DIRECTORY_SEPARATOR;
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
                static::$__libraries[$name]['path'] = array(
					static::$__libraries[$name]['path']
				);
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
     *         Defaults to \engine::GLOBAL_DEFAULT.
     *
     * @throws  InvalidArgumentException,RuntimeException
     * 
     * @return  boolean
     */
    public static function subscribe($event, \Closure $function, array $options = array()) {
        $defaults = array('shift' => false,
                          'name' => str_random(8),
                          'force' => false,
                          'namespace' => static::GLOBAL_DEFAULT);
		$options += $defaults;
		if ($options['name'] instanceof Closure) {
			$fd = false;
			do {
				$name = $options['name']();
				if (!static::hasSubscriber($event, $name, $options['namespace'])) {
					$fd = true;
				}
			} while(!$fd);
			$options['name'] = $name;
		} else {
			if (static::hasSubscriber($event,
						$options['name'],
						$options['namespace']) && !$options['force']
			) {
				throw new \RuntimeException(
					sprintf(
						'prggmr subscriber "%s" already exists; Provide "force" option to overwrite', $options['name']
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
			array_unshift_key($options['name'],
				$function, static::$__events[$options['namespace']][$event]);
		} else {
			static::$__events[$options['namespace']][$event][$options['name']] = $function;
		}
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
    public static function hasSubscriber($subscriber, $event, $namespace) {
        if (isset(static::$__events[$namespace][$event][$subscriber])) {
            return true;
        }
        return false;
    }

    /**
     * Bubbles an event.
     *
     * @param  array  $params  Parameters to directly pass to the event subscriber
     * @param  array  $options  Array of options. Avaliable options
     *
     *         `namespace` - `namespace` - Namespace for event.
     *         Defaults to engine::GLOBAL_DEFAULT.
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
     * @throws  LogicException when an error is encountered during subscriber
     *          execution
     *          RuntimeException when attempting to execute an event in
     *          an unexecutable state
     *
     * @return  object  prggmr\Event
     */
    public static function bubble($event, array $params = array(), array $options = array()) {
        $defaults  = array(
                           'namespace' => static::GLOBAL_DEFAULT,
                           'benchmark' => false,
                           'flags' => null,
                           'offset' => null,
                           'object' => false,
                           'suppress' => false
                        );
        $options  += $defaults;
        if ($event instanceof Event) {
            // Check the state of this event
            $event->setState(Event::STATE_ACTIVE);
            $eventObj = $event;
            $event = $event->getSubscription();
            $org = $event;
        } else {
            $org   = $event;
            $eventObj = new Event();
            $eventObj->setSubscription($event);
        }
        //$evreg     = '#' . $event . '$#i';
        $listeners = null;
        if (!is_array($params)) $params = array();
        if (isset(static::$__events[$options['namespace']][$event])) {
            $listeners = static::$__events[$options['namespace']][$event];
        } else if (isset(static::$__events[$options['namespace']])) {
            foreach (static::$__events[$options['namespace']] as $name => $op) {
				// my_event_:param1_:param
				$regex = $name;
				$regex = preg_replace('#:([\w]+)#i', '\(?P<$1>[\w_-]+\)', $name);
				$regex = str_replace('\(', '(', $regex);
				$regex = str_replace('\)', ')', $regex);
                $regex = '#' . $regex . '$#i';
                if (preg_match($regex, $org, $matches, $options['flags'], $options['offset'])) {
                    $listeners = static::$__events[$options['namespace']][$name];
                    $mc = count($matches);
                    if ($mc != 0) {
                        if ($mc != 1) unset($matches[0]);
						/**
						 * @todo  Fix this so dump out every other match
						 */ 
						foreach ($matches as $_k => $_v) {
							if (!is_string($_k)) {
								unset($matches[$_k]);
							}
						}
                        // take the keys from array 1 and merge them ontop of array2
                        foreach ($matches as $k => $v) {
                            $params[] = $v;
                        }
                    }
                    #$params = $matches;
                    #break;
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
                            'Event (%s) Subscriber "%s" execution failed due to exception "%s" with message "%s"',
							$event,
							$name,
							get_class($e), $e->getMessage()
                        )
                    );
                }
                // Check our event state / halt and print exception unless suppress
                if ($eventObj->getState() === Event::STATE_ERROR
					&& !$options['suppress']) {
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
                    $stats = static::analyze('bench_stop', array(
									'name' =>
									'event_'.$name.'_'.$i
									)
								);
                    if (!isset(static::$__stats['events'][$name])) {
                        static::$__stats['events'][$name] = array();
                    }
                    static::$__stats['events'][$name][] = array(
                        'results' => $return[$i],
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
                    set_error_handler('\prggmr\engine::debug');
                    set_exception_handler('\prggmr\engine::debug');
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
        if (false === PRGGMR_DEBUG) {
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
                static::bubble('benchmark_begin',$stats,array(
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
                    $data['memory'] = ($stats['memory'] > $data['memory'])
					? $stats['memory'] - $data['memory'] :
					$data['memory'] - $stats['memory'];
                    $data['time'] = $data['time'] - $stats['time'];
                    $data['start'] = $stats;
                }
                static::bubble('benchmark_stop', $data, array(
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
     * Allows for statically overloading event bubbles.
     *
     * @param  mixed  $event  Name of event to bubble
     * @param  array  $params  Array of parameters that will be passed to the
     *         event handler.
     *
     * @return  object  Event
     */
    public static function __callStatic($event, array $params = array()) {
        $defaults = array(0 => array(), 1 => array());
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

	/**
	 * Startup the prggmr framework, here we go boys and girls!
	 */
	public static function initialize(/* ... */)
	{
		// default PRGGMR_DEBUG to false
        if (!defined('PRGGMR_DEBUG')) {
            define('PRGGMR_DEBUG', false);
        }
	}
}

/**
 * Adapter Interface defines the methods for a Prggmr Engine adapter.
 */
interface AdapterInterface
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
     *         Defaults to \engine::GLOBAL_DEFAULT.
     *
     * @throws  InvalidArgumentException,RuntimeException
     * 
     * @return  boolean
     */
    public function subscribe($event, \Closure $function, array $options = array());
	
	/**
     * Bubbles an event.
     *
     * @param  array  $params  Parameters to directly pass to the event subscriber
     * @param  array  $options  Array of options. Avaliable options
     *
     *         `namespace` - `namespace` - Namespace for event.
     *         Defaults to engine::GLOBAL_DEFAULT.
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
     * @throws  LogicException when an error is encountered during subscriber
     *          execution
     *          RuntimeException when attempting to execute an event in
     *          an unexecutable state
     *
     * @return  mixed  Results of event
     * @see  engine::bubble
     */
    public function bubble($event, array $params = array(), array $options = array());
}


/**
 * Adapter
 *
 * The default prggmr Engine adapter, this class provides no additional adapter
 * functionality, only providing a bridge to the prggmr engine.
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
     *         Defaults to \engine::GLOBAL_DEFAULT.
     *
     * @throws  InvalidArgumentException,RuntimeException
     * 
     * @return  boolean
     */
    public function subscribe($event, \Closure $function, array $options = array()) {
        return engine::subscribe($event, $function, $options);
    }

    /**
     * Bubbles an event.
     *
     * @param  array  $params  Parameters to directly pass to the event subscriber
     * @param  array  $options  Array of options. Avaliable options
     *
     *         `namespace` - `namespace` - Namespace for event.
     *         Defaults to engine::GLOBAL_DEFAULT.
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
     * @throws  LogicException when an error is encountered during subscriber
     *          execution
     *          RuntimeException when attempting to execute an event in
     *          an unexecutable state
     *
     * @return  mixed  Results of event
     * @see  engine::bubble
     */
    public function bubble($event, array $params = array(), array $options = array()) {
       return engine::bubble($event, $params, $options);
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
        return engine::hasSubscriber($subscriber, $event, $namespace);
    }

    /**
     * __call directs itself to the engine bubble.
     * 
     * @see  engine::bubble
     */
    public function __call($event, array $args = array())
    {
        $defaults = array(0 => array(), 1 => array());
        $args += $defaults;
        return $this->bubble($event, $args[0], $args[1]);
    }
}


/**
 * Event
 *
 * Represents an executed/executable prggmr event.
 */
class Event extends Adapter
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
    protected $_subscriber = null;

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
        if ($this->haltSequence()) {
            return false;
        }

        if (null !== $this->_chain) {

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
     *         Defaults to engine::GLOBAL_DEFAULT.
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
     *         `stateCheck` - 
     *
     * @throws  LogicException when an error is encountered during subscriber
     *          execution
     *          RuntimeException when attempting to execute an event in
     *          an unexecutable state
     *
     * @return  mixed  Results of event
     * @see  engine::bubble
     */
    public function bubble(array $params = array(), array $options = array()) {
		$defaults = array('stateCheck' => true);
		$options += $defaults;
		//bubble chain if exists
		$this->executeChain($options['stateCheck']);
        return parent::bubble($this, $params, $options);
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
}

/**
 * Singleton implementation which provides prggmr engine adapter functionality.
 */
abstract class Singleton extends Adapter
{
    /**
     * @var  array  Instances of the singleton.
     */
    private static $_instances = array();

    /**
     * Returns instance of the called class.
     */
    final public static function instance(/* ... */)
    {
        $class = get_called_class();

        if (!isset(self::$_instances[$class])) {
            self::$_instances[$class] = new $class;
        }

        return self::$_instances[$class];
    }

    /**
     * Disallow cloning of a singleton
     */
    final private function __clone(){}
}

// The following functions are a simplified API for communicating with prggmr
// they provide no aditional functionality!

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
 *         Defaults to \engine::GLOBAL_DEFAULT.
 *
 * @throws  InvalidArgumentException,RuntimeException
 * 
 * @return  void
 */
function subscribe($event, \Closure $function, array $options = array()) {
	return engine::subscribe($event, $function, $options);
}

/**
 * Bubbles an event.
 *
 * @param  array  $params  Parameters to directly pass to the event subscriber
 * @param  array  $options  Array of options. Avaliable options
 *
 *         `namespace` - `namespace` - Namespace for event.
 *         Defaults to engine::GLOBAL_DEFAULT.
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
 * @throws  LogicException when an error is encountered during subscriber
 *          execution
 *          RuntimeException when attempting to execute an event in
 *          an unexecutable state
 *
 * @return  mixed  Results of event
 * @see  engine::bubble
 */
function bubble($event, array $params = array(), array $options = array()) {
	return engine::bubble($event, $params, $options);
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
function analyze($op, array $options = array())
{
	return engine::analyze($op, $options);
}