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
 * Bootstrap file loads the configuration and establishes
 * the prggmr.event.front event to listen on \kb30\router_dispatch.
 */
$config = \parse_ini_file('var/config/prggmr.dev.nix.ini', true);
require $config['paths']['system_path'].'/lib/kb30.php';

\Mana\KB30::analyze('bench_begin', array('name' => 'prggmr benchmark'));
// Load our configuration ini
\Mana\KB30::set('prggmr.config', $config);
// Setup our system paths
// Library Files
\Mana\KB30::library('Prggmr Library', array(
    'path'   => $config['paths']['system_path'].'/lib/',
    'prefix' => null,
    'ext'    => '.php',
    'transformer' => function($class, $namespace, $options) {
        $namespace = ($namespace == null) ? '' : str_replace('\\', DIRECTORY_SEPARATOR, $namespace).DIRECTORY_SEPARATOR;
        $class = explode('_', $class);
        if (count($class) != 3) return null;
        $class = array_map(function($i){
            return strtolower($i);
        },$class);
        $filepath = $namespace.$class[0].DIRECTORY_SEPARATOR.$class[1].'.'.$class[2];
        return $filepath;
    }
));
// Template files
\Mana\KB30::library('Prggmr Templates', array(
    'path' => $config['paths']['system_path'].'/system/var/templates/',
    'prefix' => null,
    'ext' => '.phtml',
    'transformer' => function($class, $namespace, $options) {
        if (strpos($class, '.') === false) return null;
        return str_replace('.', DIRECTORY_SEPARATOR, $class);
    }
));
// External Library files ( Uses PECL style formatting )
\Mana\KB30::library('Prggmr External');

// Setup our system library in php
spl_autoload_register('\Mana\KB30::load');

// Listen for KB30's dispatcher
\Mana\KB30::listen('router.dispatch.startup', function($uri) {
    $front = new \prggmr\Controller_Event_Front(new Renderer_Event_Output);
    $front->attach(array('uri'=>$uri));
    $front->dispatch();
    return $front;
});

if (\Mana\KB30::get('prggmr.config.system.debug')) {
    $cli = new CLI_Event_Handler($argv);
    \Mana\KB30::router('dispatch', $cli->run());
}