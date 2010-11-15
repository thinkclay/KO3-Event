<?php
namespace prggmr\event;
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
 * @category  Web
 * @copyright  Copyright (c), 2010 Nickolas Whiting
 */

/**
 * Command Line Interface handler for prggmr.
 *
 * Details provided at a later time.
 */ 
class Handle
{
    /**
     * Array of help messages outputable to the end user whenever
     * an error is encountered.
     *
     * @var  array
     */
    private $_messages = array(
        'help' => array(
            'main' => <<<HELP_TEXT
        
usage: prggmr [--processor] [--help] COMMAND [ARGS]

The current avaliable prggmr options are:
    route   Dispatch the provided route URI.
    version Displays current prggmr version.

See 'prggmr help COMMAND' for more information on a specific command.

HELP_TEXT
            ,
            'route' => <<<ROUTE_HELP
            
usage: prggmr [--processor] route [ARGS]

Triggers the prggmr event startup and provides the given URI and outputs
the results, provide the [--processor] option to modify the output processor,
the default processor used is HTML.

Example :

prggmr route "/code/view/code-sample" :

    Runs route "/code/view/(?P<slug_id>(.*))"

prggmr --processor=json route "/code/view/code-sample" :

    Runs route "/code/view/(?P<slug_id>(.*))" processing output using JSON.

ROUTE_HELP
        )
    );
    
    /**
     * Foreground command line color codes
     *
     * @var  array
     */
    private $_foreground = array(
            'black'       =>'0;30',
			'dark_gray'   =>'1;30',
			'blue'        =>'0;34',
			'light_blue'  =>'1;34',
			'green'       =>'0;32',
			'light_green' =>'1;32',
			'cyan'        =>'0;36',
			'light_cyan'  =>'1;36',
			'red'         =>'0;31',
			'light_red'   =>'1;31',
			'purple'      =>'0;35',
			'light_purple'=>'1;35',
			'brown'       =>'0;33',
			'yellow'      =>'1;33',
			'light_gray'  =>'0;37',
			'white'       =>'1;37'
        );
    
    /**
     * Background command line color codes
     *
     * @var  array
     */
    private $_background = array(
			'black'      => '40',
			'red'        => '41',
			'green'      => '42',
			'yellow'     => '43',
			'blue'       => '44',
			'magenta'    => '45',
			'cyan'       => '46',
			'light_gray' => '47'
        );
    
    /**
     * Command line arguments
     *
     * @var  array
     */
    public $argv = null;

    
    /**
     * Sets up our basic command line tool and checks all environment
     * variables.
     */
    
    public function __construct($arg)
    {
        $environment = array(
            'system' => '/system/',
            'lib_dir'=> '/lib/',
            'var'    => '/system/var/',
            'log'    => '/system/var/log/',
            'app'    => '/system/var/app/',
            'routes' => '/system/var/app/routes/',
            'log_file' => '/system/var/log/cli.log'
        );
        
        $path = \prggmr::get('prggmr.config.paths.system_path');
        
        if (!$path) {
            exit(
"Failed to load prggmr config. Please check your paths and ensure \"system/var/config/prggmr_(sys)_dev.ini\" exists.
"
            );
        }
        
        $errors = array();
        
        foreach ($environment as $k => $val) {
            if (!is_dir($path.$val) &&
                (is_file($path.$val) && !is_writeable($path.$val))) {
                $errors[] = $val;
            }
        }
        
        if (count($errors) != 0) {
            die(sprintf(
"
Failed to initilize environment please check the following paths and permissions
%s
"
            , $this->color(implode('
', $errors),'red')));
        }
        
        unset($arg[0]);
        $opt = array();
        
        foreach ($arg as $k => $v) {
            if (strpos($v, '--') !== false) {
                $ex = explode('=', $v);
                $opt[$ex[0]] = $ex[1];
                unset($arg[$k]);
            }
        }
        
        $this->options = $opt;
        $this->args = array_values($arg);
    }
    
    /**
     * Returns a string color coded for the CLI.
     *
     * @param  string  $string  String to color code.
     * @param  string  $fore  Foreground color.
     * @param  string  $back  Background color.
     *
     * @return  string  *nix color coded string
     */
    public function color($string, $fore, $back = null)
    {
        $return = "";
        if (isset($this->_foreground[$fore])) {
            $return .= "\033[" . $this->_foreground[$fore] . "m";
        }
        if ($back !== null && isset($this->_background[$back])) {
            $return .= "\033[" . $this->_background[$back] . "m";
        }
        $return .=  $string . "\033[0m";

        return $return;
    }
    
    /**
     * Runs the Command Line Interface commands.
     * Uses switch...better solution?
     */
    public function run()
    {
        if (count($this->args) == 0) {
            die($this->_messages['help']['main']);
        }
        
        // We've made it here so lets check our arguments
        switch ($this->args[0]) {
            case 'help':
                if (isset($this->_messages['help'][$this->args[1]])) {
                    die($this->_messages['help'][$this->args[1]]);
                }
                die($this->_messages['help']['main']);
                break;
            case 'route':
                $arg = $this->args[0];
                unset($this->args[0]);
                if (count($this->args) == 0) die($this->_messages['help']['route']);
                array_values($this->args);
                return $this->args[1];
                break;
            case 'version':
                die("Prggmr EMV Framework 1.0alpha");
            default:
                die($this->_messages['help']['main']);
                break;
        }
    }
    
}