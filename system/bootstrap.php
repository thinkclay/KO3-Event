<?php
namespace prggmr;
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

use prggmr\request\event as request;
use prggmr\render\event as render;
use prggmr\cli\event as cli;
use prggmr\record\connection as connection;

/************************************************************
 * Bootstrap file loads the configuration and establishes
 * the prggmr.event.front event to listen on \kb30\router_dispatch.
 */
$config = \parse_ini_file('var/config/prggmr.dev.nix.ini', true);
require $config['paths']['system_path'].'/lib/prggmr.php';

\prggmr::analyze('bench_begin', array('name' => 'prggmr benchmark'));
// Load our configuration ini
\prggmr::set('prggmr.config', $config);
// Setup our system paths
// Library Files
\prggmr::library('Prggmr Library', array(
    'path'   => $config['paths']['system_path'].'/lib/',
    'prefix' => null,
    'ext'    => '.php',
    'transformer' => function($class, $namespace, $options) {
        $namespace = ($namespace == null) ? '' : str_replace('\\', DIRECTORY_SEPARATOR, $namespace).DIRECTORY_SEPARATOR;
        $class = str_replace('_', DIRECTORY_SEPARATOR, $class);
        $filepath = strtolower($namespace.$class);
        return $filepath;
    }
));
// Template files
\prggmr::library('Prggmr Templates', array(
    'path' => $config['paths']['system_path'].'/system/var/templates/',
    'prefix' => null,
    'ext' => '.phtml',
    'transformer' => function($class, $namespace, $options) {
        if (strpos($class, '.') === false) return null;
        return str_replace('.', DIRECTORY_SEPARATOR, $class);
    }
));
// External Library files ( Uses PECL style formatting )
\prggmr::library('Prggmr External', array(
    'path' => $config['paths']['system_path'].'/lib/'
));

// Setup our system library in php
spl_autoload_register('\prggmr::load');

// Listen for KB30's dispatcher
\prggmr::listen('router.dispatch.startup', function($uri) {
    $front = new request\Dispatch(new event\Output);
    $front->attach(array('uri'=>$uri));
    $front->dispatch();
    return $front;
});

if (\prggmr::get('prggmr.config.system.debug')) {
    $cli = new cli\Handle($argv);
    \prggmr::router('dispatch', $cli->run());
}