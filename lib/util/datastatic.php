<?php
namespace prggmr\util;


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
 * @category  Render
 * @copyright  Copyright (c), 2010 Nickolas Whiting
 */

use \InvalidArgumentException;

/**
 * Static Data Class
 *
 * Mirror Image of the prggmr\util\DataInstance class in a static form,
 * with the difference being DataStatic does not implement the Iterator
 * and Countable interfaces due to its static nature.
 *
 * @see prggmr\util\DataInstance
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

        /**
         * Event call which will be used for cache.
         */
        $event = \prggmr::trigger('registry.set', array($key, $value, $overwrite), array(
                    'namespace' => get_class_name(get_called_class())));
        if (is_array($event)) {
            if ($event[0] === false) {
                return true;
            }
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
     * Returns a variable from `prggmr::$__registry`. The variable name can be
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
        /**
         * Event call which will be used for cache.
         */
        $event = \prggmr::trigger('registry.get', array($key, $options), array(
                    'namespace' =>  get_class_name(get_called_class())));
        if (is_array($event)) {
            return $event[0];
        }
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

            return (!isset(static::$__registry[$key])) ? $options['default'] : static::$__registry[$key];
        }

        throw new \InvalidArgumentException(
            sprintf(
                'Invalid arugment "$key" expected "string" received "%s"', gettype($key)
            )
        );
    }

    /**
     * Returns if a variable identified by `$key` exists in the registry.
     * `has` works just as `get` and allows for identical `$key`
     * configuration.
     * This is a mirrored shorthand of (prggmr::get($key, array('default' => false)) !== false);
     *
     * @param  $key  A string of the variable name or a "." delimited
     *         string containing the route of the array tree.
     * @return  boolean
     */
    public static function has($key) {
        /**
         * Event call which will be used for cache.
         */
        $event = \prggmr::trigger('registry.has', array($key), array(
                    'namespace' =>  get_class_name(get_called_class())));
        if (is_array($event)) {
            return $event[0];
        }
        return (static::get($key, array('default' => false)) !== false);
    }
}