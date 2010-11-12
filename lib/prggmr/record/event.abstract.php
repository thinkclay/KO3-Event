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
 * Record Event Abstract
 * 
 * Base class for all prggmr event models.
 *
 * Event models are more over a very simple abstraction layer which provides
 * simplified database interaction, at the current time select,insert,update
 * and delete methods are supported.
 *
 * While it currently does not have support for multi-relational database
 * designs it is currently being planned for development.
 *
 * Record, just as any other Activerecord DBAL identifies each object
 * as a table row with a pk (primary key) with the following purposes.
 *
 *  1. Simplified DBAL that provides a business layer seperation
 *     between our model code and business logic.
 *     
 *  2. Painless migration from database -> database. Take note that while
 *     cross database implementation exists prggmr does not create
 *     or migrate the tables itself, that is on you!
 *
 *  3. Uncoupled event driven dbal.
 *
 * The DBAL events are not to be confused with "TRIGGERS" in SQL, while
 * they do perform at the same capacity ( done via updates, insertion
 * and deletion ) the performance and useability of triggers outweighs
 * the functionality of the event based DBAL provided by prggmr.
 * That is not to say it shouldn't be used, while triggers provide a
 * incrediable interface for manipulating data in a chained sequence
 * they do not provide the support for manipulating server-side functions,
 * this is where prggmr DBAL events will shine.
 *
 * All prggmr DBAL events are triggered in the following order during a
 * select, insert, delete and update command.
 *
 * record_model_(model_name)_(operation|select|update|etc..)_before
 *     -  An event that is called upon before any SQL is executed
 *     the SQL Querystring is provided as the first parameter, along with
 *     any parameters given to the statement in an array as the second param.
 *
 * record_model_(model_name)_(operation|select|update|etc..)_after
 *     -  An event that is called upon after a SQL Querystring is executed
 *     the SQL Querystring is provided as the first parameter, along with
 *     any parameters given to the statement in an array as the second param,
 *     with the results of the query in the third param, and the lastInsertId()
 *     if one is provided as the fourth parameter.
 *     
 *     
 */
abstract class Record_Event_Abstract implements Record_Event_Interface {
}