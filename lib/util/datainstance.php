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
 * Instance Data Class
 *
 * This class allows for the manipulation and handling array data using
 * "." delimited strings rather than the old school method of
 * $array['parent']['child']['sibiling']['etc']['etc']
 *
 * The class consists of 3 methods for handling the manipulation
 * (get, set and has ).
 * It inherits the Iterator Interface allowing for transvering and
 * array like use of the class.
 */
class DataInstance implements \Iterator, \Countable
{
    /**
     * Registry property, information is stored as a `key` -> `value` pair.
     *
     * @var  array  Array of `key` -> `value` mappings for registry contents.
     */
    protected $__registry = array();

    /**
     * Indicates whether or not the current position is valid.
     *
     * @var  boolean  Indicates if the current position is valid
     */
    protected $_valid = false;

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
    public function set($key, $value = null, $overwrite = true) {

		if (null === $key) {
			return false;
		}

        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $this->set($k, $v, $overwrite);
            }
            return true;
        }

        /**
         * Event call which will be used for cache.
         */
        $event = \prggmr::trigger('registry.set', array($key, $value, $overwrite), array(
                    'namespace' => get_class_name(get_class())));
        if (is_array($event)) {
            if ($event[0] === false) {
                return true;
            }
        }

        if (true === $this->has($key) && !$overwrite) {
            return false;
        }
        if (false !== strpos($key, '.')) {
			$nodes  = explode('.', $key);
			$data =& $this->__registry;
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
            $this->__registry[$key] = $value;
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
    public function get($key, $options = array()) {
        $defaults = array('default' => false, 'tree' => true);
        $options += $defaults;
        /**
         * Event call which will be used for cache.
         */
        $event = \prggmr::trigger('registry.get', array($key, $options), array(
                    'namespace' => get_class_name(get_class())));
        if (is_array($event)) {
            return $event[0];
        }
        if (is_string($key)) {
            if (false !== strpos($key, '.')) {
                $keyArray = explode('.', $key);
                $count    = count($keyArray) - 1;
                $last     = $keyArray[$count];
                $data     = $this->__registry;
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
                if ($data !== $this->__registry) {
                    return $data;
                }
            }

            return (!isset($this->__registry[$key])) ? $options['default'] : $this->__registry[$key];
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
    public function has($key) {
        /**
         * Event call which will be used for cache.
         */
        $event = \prggmr::trigger('registry.has', array($key), array(
                    'namespace' => get_class_name(get_class())));
        if (is_array($event)) {
            return $event[0];
        }
        return ($this->get($key, array('default' => false)) !== false);
    }

    /**
     * Returns a count of the current items held in the registry stack.
     *
     * @return  integer  Number of items held in the registry stack.
     */
    public function count(/* ... */)
    {
        return iterator_count($this);
    }

    /**
     * Returns the current item.
     *
     * @return  mixed  The current item.
     */
    public function current(/* ... */)
    {
        return current($this->__registry);
    }

    /**
     * Returns the key of the current element.
     *
     * @return  scalar  Key of the current array position.
     */
    public function key(/* ... */)
    {
        return key($this->__registry);
    }

    /**
     * Checks if current position is valid
     *
     * @return  boolean  True if valid | False otherwise.
     */
    public function valid(/* ... */)
    {
        return $this->_valid;
    }

    /**
     * Rewinds the iterator to the first element
     *
     * @return  mixed  The current element after rewind.
     */
    public function rewind(/* ... */)
    {
        $this->_valid = (reset($this->__registry) !== false);
        return $this->current();
    }

    /**
     * Moves foward to the next element
     *
     * @return  mixed  The current element after next.
     */
    public function next(/* ... */)
    {
        $this->_valid = (next($this->__registry) !== false);
        return $this->current();
    }

    /**
     * Applies the given callback to all elements
     *
     * @return  boolean  True
     */
    public function each($callback)
    {
        return array_map($callback, $this->__registry);
    }

    /**
     * Applies the given filter to the registry stack.
     *
     * @return  mixed  Array of success | Null on failure
     */
    public function filter($filter)
    {
        $filter = array_filter($this->__registry, $filter);

        if (count($filter) !== 0) {
            return $filter;
        }

        return null;
    }

    /**
	 * Moves backward to the previous item.  If already at the first item,
	 * moves to the last one.
	 *
	 * @return  mixed  The current item after moving.
	 */
	public function prev(/* ... */)
    {
		if (!prev($this->__registry)) {
			end($this->__registry);
		}

		return $this->current();
	}

}