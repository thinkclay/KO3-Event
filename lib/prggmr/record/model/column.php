<?php
namespace prggmr\record\model;
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
     * Initalizes a column object
     *
     * @param  string  $name  Name of this column
     * @param  integer  $type  Type of the column
     * @param  length  $length  Max length of the column
     * @param  mixed  $default  Default value if none
     * @param  boolean  $null  Allow null values
     * @param  boolean  $pk  Column is the Primary Key
     * @param  array  $filters  Array of filters to apply
     */
    public function __construct($name, $type = 101, $length = 75, $default = '', $null = true, $pk = false, $filters = array())
    {
        
    }
}