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
 * @category  Record
 * @copyright  Copyright (c), 2010 Nickolas Whiting
 */

/************************************************************
 * Record_Adapter_Interface
 * 
 * Interface for Record Adapters
 */
interface Record_Adapter_Interface {
    
    /**
     * Constructs a SQL select statement.
     *
     * @param  array  $fields  Table fields to select, provide translations
     *         here as the key in the array.
     * @param  array  $options  NOT IMPLEMENTED
     *
     * @return  string  SQL QueryString
     */
    public static function select($fields, $options = array());
    
    /**
     * 
     */
    public static function delete();
    
    /**
     * Locates a record in the database.
     * By default all records are located using a simple
     * `a` = `b` translation, this can be modified by providing
     * a `.` delimited string containing various SQL search translators.
     *
     * Translators currently avaliable depend greatly on the adapter that is
     * currently in use, with that said here is the list of the default
     * translations.
     *
     * `between.var1.and.var2` - BETWEEN ? AND ?
     * `equal.var1` - Equals to : DEFAULT
     * `greater.var1` - Greater than
     * `greaterequal.var1` - Greater than or equal to
     * `less.var1` - Less than
     * `lessequal.var1` - Less than or equal to
     * `isnotnull.var1` - IS NOT NULL
     * `isnot.var1` - IS NOT
     * `isnull` - IS NULL()
     * `like.var1` - LIKE %?% search
     * `notequal.var1` - !=, <>
     * `notin.var1.var2.var3...' NOT IN ( Allows for unlimited vars)
     * `in.var1.var2.var3...' IN ( Allows for unlimited vars)
     * `notlike.var1` - NOT LIKE %?% search6
     *
     *  Also note that translations can be added via runtime by modifing the
     *  $find_translations array(), all translators are closures which accept
     *  a single parameter and return the querystring.
     * 
     * @param  array  $arg  Search parameters
     *
     * @return PDOStatement
     */
    public static function find();
    
    /**
     * Returns the current Primary Key (pk).
     *
     * @return  mixed  Returns the pk, or NULL if not set.
     */
    public static function pk();
}