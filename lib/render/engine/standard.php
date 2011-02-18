<?php
namespace prggmr\render\engine;


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
 * Standard template engine, this engine uses straight php to compile templates
 * and is the default engine that is shipped with Prggmr.
 *
 * This can also be used as a template for adding additional template engines.
 */
class Standard extends EngineAbstract {

    /**
     * Prggmr view paths.
     *
     * @var  array  Array of absolute paths to template files.
     */
    protected $_paths = array();

    /**
     * Prggmr Engine options.
     *
     * @var  array  Array of options used when compiling templates.
     */
    protected $_options = array(
        'extension' => 'phtml'
    );

    /**
     * Modifies a engine option, or sets it if it does not exist.
     *
     * @param  string  $option  Name of the option.
     * @param  string  $value  New value
     *
     * @return  boolean  True on success | False on failure
     */
    public function setOpt($option, $value)
    {
        if (isset($this->_options[$option])) {
            $this->_options[$option] = $value;
            return true;
        }

        $this->_options[$option] = $value;
        return true;
    }

    /**
     * Returns an engine option.
     *
     * @param  string  $option  Name of the option.
     *
     * @return  mixed  Value of option | False on non-existant
     */
    public function getOpt($option)
    {
        if (isset($this->_options[$option])) {
            return $this->_options[$option];
        }

        return false;
    }

    /**
     * Adds a path used for transvering directores when compiling templates.
     *
     * @param  mixed  $path  Absolute path | Array of paths
     * @param  array  $options  Array of options to use when adding the path
     *         Avaliable options.
     *
     *         `shift` - Push this path to the top of the path stack.
     *
     * @throws  InvalidArgumentException  Thrown when path cannot be found
     *
     * @return  boolean  True on success | False on failure
     */
    public function path($path, array $options = array())
    {
        $defaults = array('shift' => false);
        $options += $defaults;
        if (is_array($path)) {
            foreach ($path as $k => $v) {
                $this->path($v);
            }
        }

        if (!file_exists($path)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Invalid template path %s',
                    $path
                )
            );
        }

        if (true === $options['shift']) {
            array_unshift($this->_paths, $path);
        } else {
            $this->_paths[] = $path;
        }
    }

    /**
     * Returns the current template include paths.
     *
     * @return  array  Array of include paths.
     */
    public function paths()
    {
        return $this->_paths;
    }

    /**
     * Alias to `path`
     *
     * Adds a path used for transvering directores when compiling templates.
     *
     * @see prggmr\render\engine\Standard::path()
     *
     * @param  mixed  $path  Absolute path | Array of paths
     * @param  array  $options  Array of options to use when adding the path
     *         Avaliable options.
     *
     *         `shift` - Push this path to the top of the path stack.
     *
     * @throws  InvalidArgumentException  Thrown when path cannot be found
     *
     * @return  boolean  True on success | False on failure
     */
    public function addTemplatePath($path, array $options = array())
    {
        return $this->path($path, $options);
    }

    /**
     * Alias to `paths`
     *
     * Returns the current template include paths.
     *
     * @see prggmr\render\engine\Standard::paths()
     *
     * @return  array  Array of include paths.
     */
    public function getTemplatePaths()
    {
        return $this->_paths;
    }

    /**
     * Sets a variable for use when compiling templates.
     *
     * @param  mixed  $key  The variable name, an array of varibles.
     * @param  mixed  $value  The value of the variable
     * @param  array  $options  Array of options to use when setting this var.
     *
     * @return  boolean
     */
    public function assign($key, $value = null, array $options = array())
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
     * @throws  InvalidArgumentException  Thrown when the template cannot be
     *          found.
     *
     * @return  string  The compiled template.
     */
    public function compile($template, array $vars = array(), array $options = array())
    {
        $defaults = $this->_options;
        $options += $defaults;

        if (count($vars) != 0) {
            $this->assign($vars);
        }

        $found = false;

        if (false === strpos($template, '.')) {
            $template = $template . '.' . $options['extension'];
        }

        // Transverse all template directories and locate the template
        // the first instance found will be used, if no template directorys
        // have been set the autoload paths will be used

        if (count($this->_paths) == 0) {
            if (!file_exists($template)) {
                $paths = \prggmr::load($template, array('return_path' => true));
                foreach ($paths as $k => $v) {
                    if (file_exists($v)) {
                        $template = $v;
                        $found = true;
                        break;
                    }
                }
            } else {
                $found = true;
            }
        } else {
            foreach ($this->_paths as $k => $v) {
                if (file_exists($v.'/'.$template)) {
                    $found = true;
                    $template = $v.'/'.$template;
                    break;
                } else {
                    $paths[] = $v.'/'.$template;
                }
            }
        }

        if (false === $found) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Failed to locate template file %s; Directories scanned (%s)',
                    $template, implode(',', $paths)
                )
            );
        }

        // Parse the template
        ob_start();
            include $template;
            $content = ob_get_clean();
        ob_clean();

        return $content;
    }
}