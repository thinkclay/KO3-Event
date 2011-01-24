<?php
namespace prggmr\render;
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
 * @category  Render
 * @copyright  Copyright (c), 2010 Nickolas Whiting
 */

use prggmr\render\engine as engine;

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
    public function __construct()
    {
        // Assign our default engine
        $engine = new engine\Standard();
        $this->addEngine($engine, array('default' => true));
    }

    /**
     * Sets a variable for use when compiling templates.
     * Triggers the 'view_assign' event.
     *
     * @event  var_assign  Event triggered when a variable is assigned, using
     *         the view namespace. Provides the var name, value and a reference
     *         to the view object.
     *
     * @param  mixed  $key  The variable name, an array of varibles.
     * @param  mixed  $value  The value of the variable
     * @param  array  $options  Array of options to use when setting this var.
     *
     *         `event` - Boolean to trigger the "view_assign" event.
     *
     *
     * @return  boolean
     */
    public function assign($key, $value = null, array $options = array())
    {
        $defaults = array('event' => true);
        $options += $defaults;
        if (null === $key) return false;

        if(is_array($key)) {
            foreach ($key as $k => $v) {
                $this->assign($k, $v);
            }
            return true;
        }

        $this->_attributes[$key] = $value;

        if ($this->hasEngines()) {
            foreach ($this->_engines as $name => $engine) {
                $engine['object']->assign($key, $value);
            }
        }

        if ($options['event'] === true) {
            \prggmr::trigger('var_assign', array($key, $value, &$this), array(
                'namespace' => 'view'
            ));
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

    /**
     * Determain if the view has engines in its stack.
     *
     * @return  boolean
     */
    public function hasEngines()
    {
        return (count($this->_engines) == 0) ? false : true;
    }

    /**
     * Returns the avaliable template engines.
     *
     * @return  array
     */
    public function getEngines()
    {
        if ($this->hasEngines()) return $this->_engines;
        return false;
    }

    /**
     * Adds a new template engine to the engine stack.
     *
     * @param  object  $engine  The engine object
     * @param  array  $options An array of options to pass the engine
     *         while its environment is built.
     *
     *         `name` - Name to use for this engine. Defaults to class name.
     *
     *         `default` - Set this engine as the default.
     *
     *         `options` - An array of options to provide the engine.
     *
     * @exception  InvalidArgumentException  Thrown when an engine object does
     *             not extend the EngineAbstract Class.
     * @return  boolean  True on success, False Failure
     */
    public function addEngine(engine\EngineAbstract $object, array $options = array())
    {
        $defaults = array('name' => get_class_name($object), 'default' => false,
                          'options' => null);
        $options += $defaults;
        //if (!$object instanceof engine\EngineAbstract) {
        //    throw new InvalidArgumentException(
        //        sprintf(
        //            'Template engine %s must extend the EngineAbstract class'
        //        , get_class($object)
        //    ));
        //}

        if ($object->buildEnvironment($options['options'], $this) !== true) {
            return false;
        }

        $this->_engines[$options['name']]['object'] = $object;

        if ($options['default']) {
            $this->setDefaultEngine($options['name']);
        }

        return true;
    }

    /**
     * Sets the default template engine compiler.
     *
     * @param  mixed  $engine  Name or the object of the engine.
     *
     * @exception  InvalidArgumentException  Thrown when an engine object does
     *             not extend the EngineAbtsract Class.
     * @return  boolean  True on success | False on failure
     */
    public function setDefaultEngine($engine)
    {
        if (is_object($engine)) {
            if (!$engine instanceof engine\EngineAbstract) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Template engine %s must extend the EngineAbstract class'
                    , get_class($object)
                ));
            }

            $name = get_class_name($engine);
        } else {
            $name = $engine;
        }

        if ($this->engineExists($name)) {
            foreach ($this->_engines as $k => $v) {
                $this->_engines[$k]['default'] = false;
            }
            $this->_engines[$name]['default'] = true;
            return true;
        } else {
            return false;
        }
    }

    /**
     * Checks if the given engine name exists in the engine stack.
     *
     * @param  string  $name  Name of the engine.
     *
     * @return  boolean  True on success | False on failure
     */
    public function engineExists($name)
    {
        return (isset($this->_engines[$name]));
    }

    /**
     * Returns the template variables.
     *
     * @return  array  Array of template variables.
     */
    public function getTemplateVars()
    {
        return $this->_attributes;
    }
}