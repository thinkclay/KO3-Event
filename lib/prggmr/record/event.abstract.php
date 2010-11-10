<?php
namespace prggmr;
/******************************************************************************
 ******************************************************************************
 *   ##########  ##########  ##########  ##########  ####    ####  ########## 
 *   ##      ##  ##      ##  ##          ##          ## ##  ## ##  ##      ##
 *   ##########  ##########  ##    ####  ##    ####  ##   ##   ##  ##########
 *   ##          ##    ##    ##########  ##########  ##        ##  ##    ##
 * 
 *   ##    ##  ####
 *    #   #   #   #
 *     # #        #
 *      #      #######
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
 * One of the better features of Record is the event handler
 *     
 */
abstract class Record_Event_Abstract implements Record_Event_Interface {
}