<?php
namespace prggmr;
/******************************************************************************
 ******************************************************************************
 *   ##########  ##########  ##########  ##########  ####    ####  ########## 
 *   ##      ##  ##      ##  ##          ##          ## ##  ## ##  ##      ##
 *   ##########  ##########  ##    ####  ##    ####  ##   ##   ##  ##########
 *   ##          ##    ##    ##########  ##########  ##        ##  ##    ##
 * 
 *   ##    ##  ####
 *    #   #   #   #
 *     # #        #
 *      #      #######
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

/************************************************************
 * Front Event Controller
 * 
 * Handles initation of prggmr event execution, intercepts
 * the KB30::router('dispatch') triggering prggmr's interal
 * system handler.
 */
use prggmr;
use \DirectoryIterator;
class Controller_Event_Front {
    
    /**
     * Renderer Output Object.
     *
     * @var  object  Instance of Renderer_Event_Output object.
     */
    public $renderer = null;
    
    /**
     * Attaches a variable/s to the front event object.
     *
     * @param  mixed  $var  Variable name or array of vars to attach.
     * @param  mixed  $val  Value if array not provided as $var.
     *
     * @return  boolean
     */
    public function attach($var, $val = null) {
        if (is_array($var)) {
            $self = $this;
            array_walk($var, function($v, $k) use($self) {
                $self->attach($k, $v);
            });
            return true;
        }
        $this->$var = $val;
        return true;
    }
    
    /**
     * Constructor sets up the _data Renderer_Event_Output.
     * No other use needed.
     *
     * @param  object  $obj  Renderer_Event_Output
     */
    public function __construct(Renderer_Event_Output $obj)
    {
        $this->renderer = $obj;
    }
    
    /**
     * Dispatches prggmr internal event system.
     * Transverses through the prggmr/apps directory
     * and loads all apps.
     *
     * @return  true
     */
    public function dispatch()
    {
        $path  = \Mana\KB30::get('prggmr.config.paths.apps_path');
        $view  = \Mana\KB30::get('prggmr.config.paths.apps_view');
        $apps  = explode(',', \Mana\KB30::get('prggmr.config.system.installed_apps'));
        $url   = \Mana\KB30::get('prggmr.config.files.app_urls');
        $event = \Mana\KB30::get('prggmr.config.files.app_events');
        $files = array($url, $event);
        $dir   = new \DirectoryIterator($path);
        $msg   = array();
        foreach ($apps as $key => $app) {
            foreach ($files as $k => $v) {
                if (file_exists($path.'/'.$app.'/'.$v)) {
                    require $path.'/'.$app.'/'.$v;
                }
                // Setup our view template directory for this app.
                \Mana\KB30::library(sprintf('Prggmr App %s Templates', $app), array(
                    'path' => $path.'/'.$app.'/'.$views,
                    'prefix' => null,
                    'ext' => '.phtml',
                    'transformer' => function($class, $namespace, $options) {
                        if (strpos($class, '.') === false) return null;
                        return str_replace('.', DIRECTORY_SEPARATOR, $class);
                    }
                ));
            }
        }
        if (count($msg) != 0) {
            throw new \RuntimeException(
                sprintf(
                    'Failed to locate app files (%s)',
                    implode(',', $msg))
                );
        }
        \Mana\KB30::trigger('router.startup', array(), array('namespace' => 'prggmr'));
        $this->uri = str_replace(\Mana\KB30::get('prggmr_config.paths.system_web_path'), '', $this->uri);
        $this->uri = ($this->uri == '') ? '/' : $this->uri;
        #\Mana\KB30::trigger($this->uri, array($this), array('namespace' => 'prggmr'));
        
    }
    
    /**
     * Generates a Header.
     */
    public function header($code, $msg)
    {
        header($_SERVER['SERVER_PROTOCOL'].' '.$code.' '.$msg);
    }
}