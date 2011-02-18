<?php
namespace prggmr\record\connection\adapter;
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
 * @category  Record
 * @copyright  Copyright (c), 2010 Nickolas Whiting
 */


class MySQL extends Instance
{
    /**
     * Default port used for this database.
     *
     * @return  integer  Default port used for MySQL connections.
     */
    public function getDefaultPort()
    {
        return 3306;
    }
    
    /**
     * Returns driver specific attributes.
     *
     * @param  integer  $attr  Constant name of the attribute
     * 
     * @see PDO::getAttribute()
     *
     * @return  string  Value of attribute.
     */
    public function attribute($attr)
    {
        return $this->connection->getAttribute($attr);
    }
    
    /**
     * Queries for MySQL table column information.
     *
     * @param  string  $table  Name of the table.
     *
     * @return  object  PDOStatement
     */
    public function columns($table)
    {
        return $this->connection->raw(
            sprintf(
                'SHOW COLUMNS FROM %s',
                $table
            ));
    }
    
    /**
     * Queries for MySQL table information.
     *
     * @return  object  PDOStatement
     */
    public function tables()
    {
        return $this->connection->raw(
                'SHOW TABLES'
            );
    }
}