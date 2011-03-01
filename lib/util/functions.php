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
 * @package  Prggmr
 * @category  Utilities
 * @copyright  Copyright (c), 2010 Nickolas Whiting
 */

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
