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
 * Standard template engine, this engine uses straight php to compile templates
 * and is the default engine that is shipped with Prggmr.
 *
 * This can also be used as a template for adding additional template engines.
 */
class Standard extends EngineAbstract {
    
    /**
     * Sets a variable for use when compiling templates.
     *
     * @param  mixed  $key  The variable name, an array of varibles.
     * @param  mixed  $value  The value of the variable
     * @param  array  $options  Array of options to use when setting this var.     
     *
     * @return  boolean
     */
    public function assign($key, $value = null, $options = array())
    {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $this->assign($k, $v);
            }
            return true;
        }
        
        $this->$key = $value;
        return true;
    }
    
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
    public function buildEnvironment($options, $view)
    {
        // we return true to ensure our engine is added to the stack
        return true;
    }
    
    /**
     * Returns an instance of the template engines object which will allow
     * for more engine specific tasks to be performed.
     *
     * @return  object
     */
    public function getEngine()
    {
        return $this;
    }
    
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
    public function compile($template, $vars = array(), $options = array())
    {
        if (count($vars) != 0) {
            $this->assign($vars);
        }
        
        // Parse the template
        ob_start();
        include $template;
        $content = ob_get_clean();
        ob_end_clean();
        
        return $template;
    }
}