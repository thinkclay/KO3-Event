<?php
namespace prggmr\record\connection;
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

/**
 * Connections Pool
 *
 * Stores a pool of active `prggmr\record\connection\adapter\Instance`
 * Connections are added as
 * \prggmr\record\connection\Pool::add(new \prggmr\record\connection\adapter\Instance());
 */

use \InvalidArgumentException;
use \RuntimeException;
use \prggmr\record\connection\adapter as adapter;

class Pool extends \prggmr\Singleton
{
    /**
     * @var  array  The connection pool.
     */
    protected $_connections = array();
    
    /**
     * @var  boolean  Determine if pool has a default connection.
     */
    protected $_hasDefault = false;
    
    /**
     * Adds a new connection to the pool.
     *
     * @param  array  $connection  Instance of {@link connection\Instance}
     */
    public function add(adapter\Instance $connection)
    {
        if (null === $connection) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Connection Pool failed to recieve connection data'
                )
            );
        }
        
        $this->_connections[$id] = $conn;
        
        if ($this->_hasDefault) {
           $this->setDefault($id);
        }
   
    }
    
    /**
     * Returns a current connection instance
     *
     * @param  string  $id  Connection identifier
     *
     * @return \prggmr\record\connection\instance
     */
    public function get($id = null)
    {
        if (null === $id) {
            foreach (self::$_connections as $id => $conn) {
                if ($conn->isDefault()) {
                    return $conn;
                }
            }
        }
        if ($this->exists($id)) {
            return self::$_connection[$id];
        } else {
            throw new \InvalidArgumentException(
                sprintf(
                    'Unknown connection instance (%s)',
                    $id
                )
            );
        }
    }
    
    /**
     * Sets the given id as the default connection.
     *
     * @param  string  $id  Connection identifier
     *
     * @return  boolean
     */
    public function setDefault($id)
    {
        if ($this->exists($id)) {
            foreach ($this->_connection as $id => $conn) {
                $conn->isDefault(false);
            }
            $this->_connection[$id]->isDefault(true);
            return true;
        } else {
            throw new \InvalidArgumentException(
                sprintf(
                    'Unknown connection instance (%s)',
                    $id
                )
            );
        }
    }
    
    /**
     * Tests if the given connection exists
     * 
     * @param  string  $id  Connection identifier
     *
     * @return  boolean
     */
    public function exists($id)
    {
        return isset($this->_connections[$id]);
    }
    
    /**
     * Closes a connection.
     * Removes from pool but PDO has no `close`.
     *
     * @param  string  $id  Connection identifier
     */
    public function close($id)
    {
        if ($this->exists($id)) {
            $conn = $this->_connections[$id];
            unset($this->_connections[$id]);
            if ($conn->isDefault()) {
                $keys = array_keys($this->_connections);
                $this->_connections[$keys[0]]->isDefault(true);
            }
            unset($conn);
        }
        return true;
    }
}