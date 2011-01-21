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
 * @category  Web
 * @copyright  Copyright (c), 2010 Nickolas Whiting
 */

require '../var/bootstrap.php';

use \prggmr\record as record,
    \prggmr\record\model as model;

class Cars_Model extends record\Model {

    public $columns = array(
           'id' => array(
                'type' => model\Column::INTEGER,
                'length' => 11,
                'pk' => true
               ),
           'name' => array(
                'type' => model\Column::STRING,
                'length' => 20,
                'null' => false
              ),
            'number' => array(
                'type' => model\Column::INTEGER,
                'length' => 2
            )
       );
}
/**
 * Connection playground
 */
$connection = record\connection\Pool::instance();

$connection->listen('db.pool.add', function($pool, $connection, $id){
    echo "Added a new connection by ID : ".$id."\n";
});

$sqlite = new record\connection\adapter\SQLite('sqlite:my_test.sqlite');

\prggmr::listen('db.connection.success', function($conn){
   echo  "Connection Successful\n";
});

$sqlite->listen('db.raw.query.before', function($connection, $query){
    $logQuery = function() use ($query, $connection) {
        return sprintf('[ %s ] [ %s ] - %s',
                       date('Y-m-d H:i:s T'),
                       get_class_name($connection),
                       $query);
    };
    // We are now inside the current connections scope
    // calls can now be made using the $connection which is the current $connection reference
    // take a look at the following example that will log the query before it gets executed
    // and 
    if (file_exists('queries.log')) {
        $newFile = file_get_contents('queries.log');
        $newFile .= "
".$logQuery();
    } else {
        $newFile = $logQuery();
    }
    file_put_contents('queries.log', $newFile);
});

$sqlite->listen('db.raw.query.after', function($connection, $query){
    echo "DONE WITH THE RAW QUERY";
});

$connection->add($sqlite, 'Sqlite test connection');

if ($sqlite->connect()) {
    echo "JAJAJ\n\n";
}

$sqlite->raw('SELECT * FROM nowhere');

/**
 * Model Playground
 */
$model = new Cars_Model();
$model->number = 1;
$model->name   = 'This is my name!';
$model->id     = 756;