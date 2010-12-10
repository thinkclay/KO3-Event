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

use \InvalidArgumentException;
use \prggmr\record\model as model;

/**
 * Prggmr Model Table
 *
 * Represents a table within a database.
 */

class Table
{
    
    /**
     * Name of this table.
     *
     * @var  integer  Type of column
     */
    protected $_name = null;
    
    /**
     * Stack of the table's columns in the database.
     *
     * @var  array  Stack of column objects
     */
    protected $_columns = array();
    
    /**
     * Initalizes a table object
     *
     * @param  array  $columns  Columns which belong to this table.
     * @param  string  $name  Name of the table in the database.
     *
     * @throws  InvalidArgumentException
     * @return  object  prggmr\record\model\Table
     */
    public function __construct($columns, $name)
    {
        if (count($columns) === 0) {
            throw new InvalidArgumentException(
                'Invalid columns array provided'
            );
        }
        
        $this->_name = $name;
        
        $defaults = array(
                          'name'       => null,
                          'type'       => model\Column::STRING,
                          'length'     => 75,
                          'default'    => null,
                          'null'       => true,
                          'pk'         => false,
                          'validators' => array(),
                          'filters'    => array()
                          );
        
        foreach ($columns as $name => $attr) {
            $attr += $defaults;
            if (null !== $attr['name']) {
                $this->_columns[$attr['name']] = new model\Column($attr); 
            }
        }
    }
    
    /**
     * Returns the columns associated with this table.
     *
     * @return  array  Stack of columns in the database
     */
    public function getColumns()
    {
        return $this->_columns;
    }
    
    /**
     * Returns a specific column object in the table.
     *
     * @param  string  $column  Name of the column.
     *
     * @return  object  \prggmr\record\model\Column | False otherwise
     */
    public function getColumn($column)
    {
        return (isset($this->_columns[$column])) ? $this->_columns[$column] : false; 
    }
    
    /**
     * Returns the table name.
     *
     * @return  string  Name of the table.
     */
    public function getName()
    {
        return $this->_name;
    }
}