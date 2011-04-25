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

use \SplSack;


/**
 * Space
 *
 * Represents a namespace que of events.
 */
class Space extends SplQueue {

    /**
     * Name of this namespace object
     */
    protected $_namespace = null;

    /**
     * Constructs a new space object
     *
     * @param  string  $namespace  Namespace of this object
     */
    public function __construct($namespace)
    {
        $this->_namespace = $namespace;
    }
}