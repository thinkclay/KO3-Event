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

use \InvalidArgumentException;
use \prggmr\record\connection as connection;
use \prggmr\record\connection\adapter as adapter;
use \prggmr\record\model as model;
use \prggmr\record\sql as sql;

/**
 * Prggmr Models
 *
 * Base class for all Prggmr models.
 *
 * Models are defined as
 *
 * """
 *   use \prggmr\record as record,
 *   \prggmr\record\model as model;
 *
 *   class Cars_Model extends record\Model {
 *
 *       public $columns = array(
 *           'id' => array(
 *                'type' => model\Column::INTEGER,
 *                'length' => 11,
 *                'pk' => true
 *           ),
 *           'name' => array(
 *                'type' => model\Column::STRING,
 *                'length' => 20,
 *                'null' => false
 *           ),
 *            'number' => array(
 *                'type' => model\Column::INTEGER,
 *                'length' => 2
 *           )
 *       );
 *   }
 * """
 */

class Model
{
    /**
     * Defined columns for a model.
     */
    public $columns = array();

    /**
     * Stack of the model's table columns in the database.
     *
     * @var  array  Stack of column names
     */
    protected $_attributes = array();

    /**
     * Table name used for model in the database.
     *
     * @var  string  Table database name.
     */
    protected $_tableName = null;

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
     * Table object instance.
     *
     * @var  object  prggmr\record\model\Table
     */
    protected $_table = null;

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
    public function __construct($attr = array(), $conn = null, $options = array())
    {
        $defaults = array('readonly' => false, 'new' => true, 'table' => function($obj){
            return get_class_name($obj);
        });

        $options += $defaults;

        if (count($this->columns) == 0) {
            throw new \RuntimeException(
                sprintf(
                'Model "%s" does not contain column information',
                $options['table']($this)
                )
            );
        }

        //foreach ($this->columns as $name => $attr) {
        //    $attr['name'] = (isset($attr['name'])) ? $attr['name'] : $name;
        //    $this->columns[$name] = new model\Column($attr);
        //}

        if (null !== $attr && count($attr) != 0) {
            foreach ($attr as $k => $v) {
                $this->{$k} = $v;
            }
        }

        if (null !== $conn) {
            if (!$conn instanceof adapter\Instance) {
                throw new \InvalidArgumentException(
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

        $this->_attributes = $attr;

        if ($options['table'] instanceof \Closure) {
            $options['table'] = $options['table']($this);
        }

        $this->_tableName = $options['table'];

        $this->_table = new model\Table($this->columns, $this->_tableName);

        // set our primary key if it exists
        $this->pk();

        $this->_isNew = $options['new'];
        $this->_readOnly = $options['readonly'];
    }

    /**
     * Overload __set into our data properties.
     *
     *
     */
    public function __set($name, $value)
    {
        return $this->attribute($name, $value);
    }

    /**
     * Overload __get into our table columns object.
     *
     * @see \prggmr\record\model\Column
     * @return  mixed  Value of the column | Null if empty | False if not exist
     */
    public function __get($name)
    {
        $col = $this->_table->getColumn($name);

        if (false === $col) {
            return false;
        }

        return $col->getValue();
    }

    /**
     * Sets a columns value.
     *
     * @param  string  $column  Name of the column.
     * @param  mixed  $value  Value to set.
     *
     * @return  mixed  Model object on success | False otherwise
     */
    public function attribute($column, $value)
    {
        $col = $this->_table->getColumn($column);

        if (false === $col) {
            return false;
        }

        if (!$col->set($value, $this->_connection)) {
            return false;
        }

        return $this;
    }


    /**
     * Returns the connection instance.
     *
     * @see prggmr\record\connection\adapter\Instance
     * @return  object  prggmr\record\connection\adapter\Instance
     */
    public function getConnection()
    {
        return $this->_connection;
    }

    /**
     * Returns the readonly status.
     *
     * @return  boolean  True on readonly | False otherhwise
     */
    public function isReadOnly()
    {
        return $this->_readOnly;
    }

    /**
     * Returns the new row status.
     *
     * @return  boolean  True on new row | False otherwise
     */
    public function isNew()
    {
        return $this->_isNew;
    }

    /**
     * Returns the primary key column object. False if model doesn't have.
     *
     * @see prggmr\record\model\Column
     * @return  object  Column object of pk | False otherwise.
     */
    public function pk()
    {
        if (null === $this->_pk) {
            if (null === $this->_table) {
                return false;
            }
            foreach ($this->_table->getColumns() as $col => $obj) {
                if ($obj->isPk()) {
                    $this->_pk = $obj;
                    return $this->_pk;
                }
            }
            return false;
        }

        return $this->_pk;
    }

    /**
     * Returns the table object assoicated with model.
     *
     * @see prggmr\record\model\Table
     * @return  object  prggmr\record\model\Table
     */
    public function table()
    {
        return $this->_table;
    }

    /**
     * Write the current model to the database table as a new, or
     * update an existing record.
     *
     * @return  boolean  True on success | False otherwise
     */
    public function save()
    {
        if ($this->isReadOnly()) {
            return false;
        }

        $querystring = new sql\QueryBuilder($this);
    }
}