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

use \prggmr\util as util;

/**
 * Connection Instance
 */
abstract class Instance extends util\Listenable {

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
    protected $_tested = null;

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
     * @return  object
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
     * @event  record_connection_adapter_transaction
     *      @param  object   Adapter object initalizing the transaction.
     *
     * @throws  RuntimeException
     * @return  boolean
     */
    public function transaction()
    {
        if (!$this->connection->beginTransaction()) {
            throw new RuntimeException($this->connection->errorInfo,
                                       intval($this->connection->errorCode));
        }
        $this->trigger('db.transaction', array($this), array(
            'namespace' => \prggmr::GLOBAL_DEFAULT
        ));
        return true;
    }

    /**
     * Commits a transaction.
     *
     * @event  record_connection_adapter_commit
     *      @param  object  $obj  Adapter object pushing the commit.
     *
     * @throws  RuntimeException
     * @return  boolean
     */
    public function commit()
    {
        if (!$this->connection->commit()) {
            throw new RuntimeException($this->connection->errorInfo,
                                       intval($this->connection->errorCode));
        }
        $this->trigger('db.commit', array($this), array(
            'namespace' => \prggmr::GLOBAL_DEFAULT
        ));
        return true;
    }

    /**
     * Tests and establishes the database connection, otherwise result of
     * previous test will be returned.
     *
     * @event  test
     *      @param  object  $obj  Adapter object performing the test
     * @throws  RuntimeException
     * @return  boolean  True on success | False on failure
     */
    public function connect()
    {
        $this->trigger('db.connection.test', array($this), array(
            'namespace' => \prggmr::GLOBAL_DEFAULT
        ));

        if (null === $this->_tested) {
            // Connection not tested, test the connection
            try {
                $this->connection = new \PDO($this->dsn,
                                            $this->user,
                                            $this->password,
                                            $this->options);
            } catch (Exception $e) {
                $this->trigger('db.connection.failure', array($this), array(
                    'namespace' => \prggmr::GLOBAL_DEFAULT
                ));
                throw new RuntimeException($this->connection->errorInfo,
                                       intval($this->connection->errorCode));
            }
            $this->_tested = true;
        }
        $this->trigger('db.connection.success', array($this), array(
            'namespace' => \prggmr::GLOBAL_DEFAULT
        ));
        return $this->_tested;
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
     * @event  db_raw_query_before  Triggered on call, returning false will
     *         cause method to halt.
     *     @param  object  Connection Instance
     *     @param  string  SQL Statement Executed
     *
     * @event  db_raw_query_after
     *     @param  object  Connection Instance
     *     @param  string  SQL Statement Executed
     *     @param  object  Any results returned from the query
     *             {@link PDOStatement}
     *
     * @return  boolean
     */
    public function raw($statement, $pdo_fetch = null, $arg2 = null, $arg3 = null)
    {
        try {
            $this->trigger('db.raw.query.before', array($this, $statement), array(
                'errors' => true,
                'namespace' => \prggmr::GLOBAL_DEFAULT
            ));
        } catch (\RuntimeException $e) {
            return false;
        }
        try {
            $query = $this->connection->query($statement, $pdo_fetch, $arg2, $arg3);
        } catch (PDOException $e) {
            throw new RuntimeException($this->connection->errorInfo,
                                       intval($this->connection->errorCode));
        }
        $this->trigger('db.raw.query.after', array($this, $statement, clone (object) $query), array(
            'namespace' => \prggmr::GLOBAL_DEFAULT
        ));
        $this->querystring = $statement;
        return $query;
    }

    /**
     * Default port used for this database.
     *
     * @return  integer  Default port used for database connection.
     */
    abstract public function getDefaultPort();

    /**
     * Returns driver specific attributes.
     *
     * @param  integer  $attr  Constant name of the attribute.
     *
     * @see PDO::getAttribute()
     *
     * @return  string  Value of attribute.
     */
    abstract public function attribute($attr);

    /**
     * Queries for database table column information.
     *
     * @param  string  $table  Name of the table.
     *
     * @return  object  PDOStatement
     */
    abstract public function columns($table);

    /**
     * Queries for database table information.
     *
     * @return  object  PDOStatement
     */
    abstract public function tables();
}