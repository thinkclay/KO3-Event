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

/**
 * Connection Instance
 */
abstract class Instance {
    
    /**
     * @var  object  PDO Connection instance {@link PDO}
     */
    public $connection = null;
    
    /**
     * @var  string  DSN Connection string
     */
    public $dsn = null;
    
    /**
     * var  string  Current user connected
     */
    public $user = null;
    
    /**
     * var  int  Current port connection is established through
     */
    public $port = null;
    
    /**
     * var  str  Current password used for this connection
     */
    public $password = null;
    
    /**
     * var  array  Options used for this connection
     */
    public $options = null;
    
    /**
     * var  boolean  Flag determaining if this conneciton has ben tested
     */
    public $tested = false;
    
    /**
     * @var  string  Quote Indentifier used for tables,fields
     */
    public $quote_identifier = '`';
    
    /**
     * @var  string  Querystring of the last run query
     */
    public $querystring = null;
    
    /**
     * @var  object  Instance of \prggmr\Log
     */
    public $log = null;
    
    /**
     * @var  boolean  Is the default connection instance
     */
    protected $_default = false;
    
    /**
     * Init a new connection.
     *
     * @param  string  $dsn  DSN Querystring used for this connection
     * @param  string  $usr  The username that will used for connection
     * @param  string  $pwd  The user's password
     * @param  array   $options  Array of options for this connection.
     *
     * @event  prggmr\record_connection_add  $this
     *
     * @return  Instance
     * 
     */
    public function __construct($dsn, $usr = null, $pwd = null, $options = null)
    {
        $this->dsn = $dsn;
        $this->user = $usr;
        $this->password = $pwd;
        $this->options = $options;
        $port = preg_match('/^port=([0-9]+)$/', $dsn);
        if (!isset($port[0])) {
            $this->port = $this->getDefaultPort();
        } else {
            $this->port = $port[0];
        }
        
        \prggmr::trigger('record_connection_add', array($this));
        
        return true;
    }

    
    /**
     * Sets or returns if instance is the default.
     *
     * @param  mixed  $arg  True,False sets default, null returns status
     *
     * @return  boolean
     */
    public function isDefault($arg = null)
    {
        if (null === $arg) {
            return $this->_default;
        }
        var_dump($arg);
        $this->_default = $arg;
        return $this->_default;
    }
    
    /**
     * Returns the string quoted using the $quote_identifier
     *
     * @param  string  $string  String to quote
     */
    public function quote($string)
    {
        $pre = $string[strlen($string)-1] === $this->quote_identifier;
        $app = $string[0] === $this->quote_identifier;
        if (!$app) {
            $string = $this->quote_identifier . $string;
        }
        if (!$pre) {
            $string = $string . $this->quote_identifier; 
        }

        return $string;
    }
    
    /**
     * Returns a datetime object formatted in the database's date format
     *
     * @param  object  $date  Datetime object
     * 
     * @return  string  Database date format
     */
    public function date($date)
    {
        return $date->format('Y-m-d');
    }
    
    /**
     * Returns a datetime object formatted in the database's datetime format
     *
     * @param  object  $date  Datetime object
     * 
     * @return  string  Database date format
     */
    public function datetime($date)
    {
        return $date->format('Y-m-d H:i:s T');
    }
    
    /**
     * Returns the insertid from a previous transaction.
     *
     * @return  interger  Id of last transaction
     */
    public function insertid()
    {
        return $this->connection->lastInsertId();
    }
    
    /**
     * Begins a transaction.
     *
     * @return  boolean
     */
    public function transaction()
    {
        if (!$this->connection->beginTransaction()) {
            throw new RuntimeException($this->connection->errorInfo,
                                       intval($this->connection->errorCode));
        }
        return true;
    }
    
    /**
     * Commits a transaction.
     *
     * @return  boolean
     */
    public function commit()
    {
        if (!$this->connection->commit()) {
            throw new RuntimeException($this->connection->errorInfo,
                                       intval($this->connection->errorCode));
        }
        return true;
    }
    
    /**
     * Executes a raw sql query and returns a {@link PDOStatement}
     * result.
     *
     * @param  string  $statement  SQL Query statement to execute
     * @param  const   $pdo_fetch  PDO Fetch method constant
     *         PDO::FETCH_COLUMN
     *         PDO::FETCH_INTO
     *         PDO::FETCH_CLASS
     * @see  http://us.php.net/manual/en/pdo.query.php
     *
     * @event  \prggmr\record_raw
     *     @param  object  $this  Connection Instance
     *     @param  string  $statement  SQL Statement Executed
     *     @param  object  $results  Any results returned from the query
     *             {@link PDOStatement}
     *
     * @return  boolean
     */
    public function raw($statement, $pdo_fetch = null, $arg2 = null, $arg3 = null)
    {
        try {
            $query = $this->connection->query($statement, $pdo_fetch, $arg2, $arg3);
        } catch (PDOException $e) {
            throw new RuntimeException($this->connection->errorInfo,
                                       intval($this->connection->errorCode));
        }
        $this->querystring = $statement;
        \prggmr::trigger('record_raw', array($this, $statement, clone $query));
        return $query;
    }
    
    /**
     * Default port used for this database
     */
    abstract public function getDefaultPort();
}