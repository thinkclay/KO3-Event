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

/************************************************************
 * Dispatch event handler
 * 
 * Handles initation of prggmr event execution, intercepts
 * the KB30::router('dispatch') triggering prggmr's interal
 * system dispatcher, and is then passed as the first parameter
 * to any subsequent event listeners as the "Request".
 *
 * Triggers the `prggmr.router.startup`, `prggmr.uri` events.
 */
use renderer\event;
use \DirectoryIterator;

class Dispatch {
    
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
     *
     * @param  object  $obj  Renderer_Event_Output
     */
    public function __construct(Output $obj)
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
        $path  = \prggmr::get('prggmr.config.paths.apps_path');
        $view  = \prggmr::get('prggmr.config.paths.apps_views');
        $apps  = explode(',', \prggmr::get('prggmr.config.system.installed_apps'));
        $url   = \prggmr::get('prggmr.config.files.app_urls');
        $event = \prggmr::get('prggmr.config.files.app_events');
        $files = array($url, $event);
        $dir   = new \DirectoryIterator($path);
        $msg   = array();
        foreach ($apps as $key => $app) {
            foreach ($files as $k => $v) {
                if (file_exists($path.'/'.$app.'/'.$v)) {
                    require $path.'/'.$app.'/'.$v;
                }
                // Setup our view template directory for this app.
                \prggmr::library(sprintf('Prggmr App %s Templates', $app), array(
                    'path' => $path.'/'.$app.'/'.$view,
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
        \prggmr::trigger('router.startup', array(), array('namespace' => 'prggmr'));
        $this->uri = str_replace(\prggmr::get('prggmr_config.paths.system_web_path'), '', $this->uri);
        $this->uri = ($this->uri == '') ? '/' : $this->uri;
        \prggmr::trigger($this->uri, array($this), array('namespace' => 'prggmr'));
        
    }
    
    /**
     * Generates a HTTP Header.
     * 
     * @param  int  $code  HTTP Header Code to generate
     *
     * @return  boolean
     */
    public function header($code)
    {
        $codes = array(
            // 2xx Codes
            '200' => 'Ok',
            '201' => 'Created',
            '202' => 'Accepted',
            '203' => 'Non-Authoritative Information',
            '204' => 'No Content',
            '205' => 'Reset Content',
            '206' => 'Partial Content',
            // 3xx Codes
            '300' => 'Multiple Choices',
            '301' => 'Moved Permanently',
            '302' => 'Found',
            '303' => 'See Other',
            '304' => 'Not Modified',
            '305' => 'Use Proxy',
            '306' =>  false,  //unused reserved code @throws Notice
            '307' => 'Temporary Redirect',
            // 4xx Codes
            '400' => 'Bad Request',
            '401' => 'Unauthorized',
            '402' => 'Payment Required', // Unused reserved code @throws Notice
            '403' => 'Forbidden',
            '404' => 'Not Found',
            '405' => 'Method not allowed',
            '406' => 'Not Acceptable',
            '407' => 'Proxy Authentication Required',
            '408' => 'Request Timeout',
            '409' => 'Conflict',
            '410' => 'Gone',
            '411' => 'Length Required',
            '412' => 'Precondition Failed',
            '413' => 'Request Entity Too Large',
            '414' => 'Request-URI to Long',
            '415' => 'Unsupported Media Type',
            '416' => 'Request Range Not Satisfiable',
            '417' => 'Expectation Failed',
            // 5xx Codes
            '500' => 'Internal Server Error',
            '501' => 'Not Implemented',
            '502' => 'Bad Gateway',
            '503' => 'Service Unavaliable',
            '504' => 'Gateway Timeout',
            '505' => 'HTTP Version Not Supported'
        );
        
        if (isset($codes[$code])) {
            header($_SERVER['SERVER_PROTOCOL'].' '.$code.' '.$codes[$code]);
            return true;
        }
        return false;
    }
}