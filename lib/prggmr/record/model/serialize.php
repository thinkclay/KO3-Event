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

use \InvalidArgumentException,
\DateTime,
\Closure,
\prggmr\util as util,
\prggmr\record as record;
/**
 * Prggmr Model Columns
 *
 * Represents a column within a table.
 */

class Serialize extends util\Singleton
{
    /**
     * Array of cached serialized model objects.
     *
     * @var  array  Stack of cache model objects.
     */
    protected $_cache = array();

    /**
     * Serializes a \prggmr\record\Model object using XML, JSON or
     * PHP Serialization.
     *
     * Model objects are serialized by using.
     *
     * """
     *      $model = new \myapp\Cars\Model();
     *      $model->find_by_type('Honda');
     *      $serial = \prggmr\record\model\Serialize::getInstance();
     *      $serialized = $serial->serialize($model);
     * """
     *
     * Serialized objects can be parsed back into \prggmr\record\Model objects
     * by using.
     *
     *  """
     *      $serial = \prggmr\record\model\Serialize::getInstance();
     *      $serial->deserialize($serialized);
     *  """
     *
     * @param  object  $model  \prggmr\record\Model
     * @param  string  $mode  Serialization mode. Avaliable modes.
     *
     *         `xml` - XML Encoded
     *
     *         `json` - JSON Encoded ** DEFAULT **
     *
     *         `php`  - PHP Serialization utility
     *
     * @param  array  $options  Options to use when serialization the model.
     *         Avaliable options.
     *
     *         `options` - Retains the models options e.g. read-only, dirty.
     *         [Default: true]
     *
     *         `cache` - Pull the object from the cache if exists.
     *         [Default: false]
     *
     * @event  record_model_serialize
     *      @param  object  \prggmr\record\Model object being serialized.
     *      @param  object  \prggmr\record\Serialize object.
     *
     * @throws  InvalidArgumentException
     * @returns  string  Serialized model string representation.
     */
    public function serialize(record\Model $model, $mode = 'json', $options = array()) {
        $defaults = array('options' => true, 'cache' => false);
        $options += $defaults;
        $name = get_class_name($model);

        $serializers = array(
            'json' => function($model) use (&$options) {

            },
            'xml' => function($model) use (&$options) {

            },
            'php' => function($model) use (&$options) {

            }
        );

        if (!isset($serializers[$mode])) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Invalid serialization mode "%s"; Avaliable modes (%s)',
                    $mode,
                    implode(',', array_keys($serializers))
                )
            );
        }
    }

    /**
     * Parses a serialized \prggmr\record\Model object, returning a new
     * \prggmr\record\Model object.
     *
     * @param
     */
    public function deserialize() {

    }
}