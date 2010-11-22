<?php
namespace prggmr\request\event;
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
 * @category  System
 * @copyright  Copyright (c), 2010 Nickolas Whiting
 */

/**
 * Prggmr View
 * 
 * The view layer is responsible for handling the output of a prggmr cycle
 * and is typically the last operating performed during a cycle, which will
 * allow the view to "validate" various objects via a common interface before
 * the content is rendered to the user, this can be operations such as insuring
 * the response object has generated the header responses before outputting any
 * data.
 */
class View {
    
    /**
     * An array which stores our current engines stack.
     *
     * @var  array  Engines Stack
     */
    protected $_engines = array();
    
    /**
     * An array of variables which will be used when compiling templates.
     *
     * @var  array  View template variables
     */
    protected $_attributes = array();
    
    /**
     * An array of callable filters.
     *
     * @var  array  View filters
     */
    protected $_filters = array();
    
    /**
     * Constructor.
     * 
     */
    public function __construct(){}
    
    /**
     * Sets a variable for use when compiling templates.
     *
     * @param  mixed  $key  The variable name, an array of varibles.
     * @param  mixed  $value  The value of the variable
     *
     * @return  boolean
     */
    public function assign($key, $value = null)
    {
        if (null === $key) return false;
        
        if(is_array($key)) {
            foreach ($key as $k => $v) {
                $this->assign($k, $v);
            }
            return true;
        }
        
        $this->_attributes[$key] = $value;
        
        if ($this->hasEngines()) {
            foreach ($this->getEngines() as $name => $engine) {
                $engine->assign($key, $value);
            }
        }
    }
    
    /**
     * __set overloading assigns template variables.
     *
     * @return  boolean
     */
    public function __set($name, $value)
    {
        return $this->assign($name, $value);
    }
    
    /**
     *__get overloading returns template variables.
     *
     * @return  mixed
     */
    public function __get($name)
    {
        if (isset($this->_attributes[$name])) {
            return $this->_attributes[$name];
        }
        return false;
    }
}