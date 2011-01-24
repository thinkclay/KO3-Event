<?php
namespace prggmr\render\engine;
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

/**
 * View template engines are really nothing more than an abstraction layer
 * to communicate prggmr to an external template library such as Smarty.
 * The engines themselves are rather quite simple, contrary to their name
 * as they are provided 4 main methods, compile a template, assigning a
 * template variable, building the environment and return the templates
 * object for performing more complex operations which cannot be standardized.
 *
 */
abstract class EngineAbstract {

    /**
     * Sets a variable for use when compiling templates.
     *
     * @param  mixed  $key  The variable name, an array of varibles.
     * @param  mixed  $value  The value of the variable
     * @param  array  $options  Array of options to use when setting this var.
     *
     * @return  boolean
     */
    abstract public function assign($key, $value = null, array $options = array());

    /**
     * Setup the environment for the template engine.
     * This must return a boolean true of the engine will not be added to
     * the engines stack.
     *
     *
     * @param  array  $options   Array of options passed to the engine.
     * @param  object  $view  View object which called this engine.
     *
     * @return  boolean
     */
    abstract public function buildEnvironment($options, $view);

    /**
     * Returns an instance of the template engines object which will allow
     * for more engine specific tasks to be performed.
     *
     * @return  object
     */
    abstract public function getEngine();

    /**
     * Compiles the provided template using the template engines compiler
     * and returns the results.
     * This must return a string or the return will be ignored.
     *
     * @param  string  $template  The template filename.
     * @param  array  $vars  An array of variables to use when compilling
     *         the template.
     * @param  array  $options An array of options passed to the template
     *         engine.
     *
     * @return  string  The compiled template.
     */
    abstract public function compile($template, array $vars = array(), array $options = array());
}