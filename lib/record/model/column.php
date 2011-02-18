<?php
namespace prggmr\record\model;


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
use \DateTime;
use \Closure;
use \prggmr\record\connection\adapter as adapter;
/**
 * Prggmr Model Columns
 *
 * Represents a column within a table.
 */

class Column
{
    /**
     * Column Data Types Constants.
     *
     * These are the standards used for defining prggmr models.
     */
    const STRING   = 101;
    const INTEGER  = 102;
    const FLOAT    = 103;
    const DECIMAL  = 104;
    const TEXT     = 105;
    const DATETIME = 106;
    const DATE     = 107;
    const TIME     = 108;

    /**
     * Mappings used for error handling.
     *
     * @var  array  Stack of column type human friendly.
     */
    protected $_maps = array(
        101 => 'STRING',
        102 => 'INTEGER',
        103 => 'FLOAT',
        104 => 'DECIMAL',
        105 => 'TEXT',
        106 => 'DATETIME',
        107 => 'DATE',
        108 => 'TIME'
    );

    /**
     * Type of this column.
     *
     * @var  integer  Type of column
     */
    protected $_type = null;

    /**
     * Is this column the PK.
     *
     * @var  boolean  Flag for PK.
     */
    protected $_pk = false;

    /**
     * Allow null values for this column.
     *
     * @var  boolean  Allow null
     */
    protected $_null = true;

    /**
     * Name of this column.
     *
     * @var  string  Name of the column
     */
    protected $_name = null;

    /**
     * Maximum Length of this column.
     *
     * @var  integer  Max length of column value
     */
    protected $_length = null;

    /**
     * Default value for column.
     *
     * @var  mixed  Default value
     */
    protected $_default = null;

    /**
     * Set of filters to apply before a insert/update on this column.
     *
     * @var  array  Array of filters.
     */
    protected $_filters = array();

    /**
     * Determains if the columns filters have run.
     *
     * @var  boolean  Flag for filters invoked.
     */
    protected $_filtersInvoked = false;

    /**
     * Stack of validators to use when validating this column.
     *
     * @var  array  Stack of validators.
     */
    protected $_validators = array();

    /**
     * Current value of this column.
     *
     * @var  mixed  Value of the column.
     */
    protected $_value = null;

    /**
     * Initalizes a column object
     *
     * @param  array   $options  Array of options. Avaliable options
     *
     *         `name` - Name of column.
     *
     *         `type` - Type of column.
     *
     *         `length` - Maximum length
     *
     *         `default` - Default value
     *
     *         `null`  - Allow null values
     *
     *         `pk`  -  Column is the PK
     *
     *         `filters`  -  Array of filters to apply to column upon
     *         insert/update commits.
     *
     *         `validators`  -  Array of validators to invoke upon
     *         insert/update commits.
     *
     * @throws  InvalidArgumentException
     */
    public function __construct($options = array())
    {

        $defaults = array(
                    'name'       => null,
                    'type'       => self::STRING,
                    'length'     => 75,
                    'default'    => null,
                    'null'       => true,
                    'pk'         => false,
                    'validators' => array(),
                    'filters'    => array()
                    );
        $options += $defaults;

        if (null === $options['name']) {
            throw new \InvalidArgumentException (
                \prggmr::get('prggmr.i18n.exceptions.record_column_noname')
            );
        }

        $typecheck = false;
        for ($i=0;$i!=8;$i++) {
            if ($options['type'] == (101 + $i)) {
                $typecheck = true;
            }
        }
        if (false == $typecheck) {
            throw new \InvalidArgumentException(
                sprintf(
                    \prggmr::get('prggmr.i18n.exceptions.record_invalid_column'),
                    $options['type'],
                    implode(",", $this->_maps)
                )
            );
        }

        $this->_name    = $options['name'];
        $this->_type    = $options['type'];
        $this->_length  = $options['length'];
        $this->_default = $options['default'];
        $this->_null    = $options['null'];
        $this->_validators = $options['validators'];
        $this->_pk      = $options['pk'];
        $this->_filters = $options['filters'];
    }

    /**
     * Returns if column is the Primary key
     *
     * @return  boolean  True if primary | False otherwise
     */
    public function isPk()
    {
        return $this->_pk;
    }

    /**
     * Adds a new filter to run on the column.
     *
     * @param  object  $filter  Closure of filter to run. Excepts 1 Parameter.
     *         A variable reference to the column value.
     *
     * @return  boolean  True on success | False otherwise
     */
    public function filter(\Closure $obj)
    {
        if (!$obj instanceof \Closure) {
            return false;
        }

        $this->_filters[] = $obj;
    }

    /**
     * Runs a columns filters.
     *
     * @return  mixed  Value of the column after invoking filters.
     */
    public function invokeFilters()
    {
        if (true == $this->_filtersInvoked) {
            return $this->value;
        }

        if (count($filters) != 0) {
            foreach ($this->_filters as $k => $v) {
                if ($v instanceof \Closure) {
                    $v(&$this->_value);
                }
            }
        }

        $this->_filtersInvoked = true;

        return $this->_value;
    }

    /**
     * Returns the type of column.
     *
     * @return  integer  Type of column
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Returns the default value of column.
     *
     * @return  mixed  Default value of column
     */
    public function getDefault()
    {
        return $this->_default;
    }

    /**
     * Returns the value of column.
     *
     * @return  mixed  Value of column
     */
    public function getValue()
    {
        return $this->_value;
    }

    /**
     * Returns the length of column.
     *
     * @return  mixed  Value of column
     */
    public function getLength()
    {
        return $this->_length;
    }

    /**
     * Returns column name.
     *
     * @return  string  Name of column
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Validates a columns value before insertion.
     *
     * @return  mixed  Value of column | Array of errors.
     */
    public function validate()
    {
        // Invoke filters before validating the field.
        if (false == $this->_filtersInvoked) {
            $this->invokeFilters();
        }

        $errors = array();

        if (count($this->_validators) != 0) {
            foreach ($this->_validators as $k => $v) {
                #if ($v instanceof validate\ValidatorAbstract) {
                #    if (false === $v->validate($this->_value)) {
                #        $errors[] = $v->getErrors();
                #    }
                #}
                if ($v instanceof \Closure) {
                    $valid = $v($this->_value);
                    if (true !== $valid) {
                        $errors[] = $valid;
                    }
                }
            }
        }

        if (null === $this->_value && false === $this->_null) {
            $errors[] = \prggmr::get('prggmr.i18n.exceptions.record_column_null_value');
        }

        if (count($errors) != 0) {
            return $errors;
        }

        return $this->_value;
    }

    /**
     * Sets a columns value, casting the value before its set.
     * If null value given the default will be used.
     *
     * @param  mixed  $value  Value to set.
     * @param  object  $connection  \prggmr\record\connection\Instance
     *
     * @throws  InvalidArgumentException
     * @return  boolean  True on success | False otherwise
     */
    public function set($value, adapter\Instance $connection = null)
    {
        if (null === $value) {
            $default = $this->getDefault();
            if ($default instanceof \Closure) {
                $default = $default($this);
            }
        }
        switch ($this->_type) {
            case static::STRING:
            case static::TEXT:
                $this->_value = (string) $value;
                return true;
                break;
            case static::DECIMAL:
                $this->_value = (double) $value;
                return true;
                break;
            case static::FLOAT:
                $this->_value = (float) $value;
                return true;
                break;
            case static::INTEGER:
                $this->_value = (integer) $value;
                return true;
                break;
            case static::DATE:
            case static::DATETIME:
            case static::TIME:
                if (!$connection instanceof adapter\Instance) {
                    throw new InvalidArgumentException(
                        sprintf(
                            \prggmr::get('prggmr.i18n.exceptions.record_column_date_cast')
                            ,
                            $this->getName(),
                            get_class_name($connection)
                        )
                    );
                }

                if (!$value instanceof \DateTime) {
                    $value = new \DateTime($value);
                }

                if ($this->_type == static::DATE) {
                    $this->_value = $connection->date($value);
                } else {
                    $this->_value = $connection->datetime($value);
                }
                return true;
                break;
        }
        return true;
    }
}