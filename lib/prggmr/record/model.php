<?php
namespace prggmr\record;
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

use \prggmr\record\connection as connection;
use \prggmr\record\connection\adapter as adapter;

/**
 * Prggmr Models
 *
 * Base class for all Prggmr models.
 */

class Model
{
    /**
     * Stack of the model's table columns in the database.
     *
     * @var  array  Stack of column names
     */
    protected $_columns = array();
    
    /**
     * Table name used for model in the database.
     *
     * @var  string  Table database name.
     */
    protected $_table = null;
    
    /**
     * Write status of the model.
     *
     * @var  boolean  If model needs to write.
     */
    protected $_isDirty = false;
    
    /**
     * Read-only status of the model.
     *
     * Once a model goes into read-only it cannot be reversed.
     *
     * @var  boolean  Read-only status.
     */
    protected $_readOnly = false;
    
    /**
     * Determains if the model is a new record.
     *
     * @var  boolean  New record to insert.
     */
    protected $_isNew = true;
    
    /**
     * prggmr\record\connection\adapter\Instance to use for transactions.
     * 
     * If null the default connection pool is used.
     *
     * @see prggmr\record\connection\adapter\Instance
     * @var  mixed  prggmr\record\connection\adapter\Instance|Null for default
     */
    protected $_connection = null;
    
    /**
     * Primary key column for model.
     *
     * @var  string  Primary key column name.
     */
    protected $_pk = null;
    
    /**
     * Name of database model uses.
     *
     * @var  string  Database name model associates.
     */
    protected $_database = null;
    
    /**
     * Initalize the model.
     *
     * @param  array  $attr  Attributes to set to the model
     * @param  object  $conn  prggmr\record\connection\adapter\Instance
     * @param  boolean $readonly  Read-only flag, allows to find() a result
     *
     * @event  record_model_init
     *      @param  object  Model instance
     * 
     * @throws  InvalidArgumentException
     * @return  object  prggmr\record\Model
     */
    public function __construct($attr = array(), $conn = null, $readonly = false)
    {
        if (count($attr) != 0) {
            foreach ($attr as $k => $v) {
                $this->{$k} = $v;
            }
        }
        
        if (null !== $conn) {
            if (!$conn instanceof adapter\Instance) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Invalid connection instance; Expected instance of %s recieved %s',
                        'prggmr\record\connection\adapter\Instance',
                        get_class($conn)
                    )
                );
            }
            $this->_connection = $conn;
        } else {
            $this->_connection = connection\Pool::instance()->getConnection();
        }
    }
    
    /**
     * Overload __set into our data properties.
     *
     * 
     */
    public function __set($name, $value)
    {
        $this->attribute($name, $value);
    }
    
    /**
     * Returns the connection instance.
     *
     * @see prggmr\record\connection\adapter\Instance
     * @return  object  prggmr\record\connection\adapter\Instance
     */
    public function getConnection()
    {
        return $this->conn;
    }
}