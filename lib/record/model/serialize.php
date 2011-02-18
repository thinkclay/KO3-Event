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
    public function serialize(record\Model $model, $mode = 'json', array $options = array()) {
        $defaults = array('options' => true, 'cache' => false);
        $options += $defaults;
        $name = get_class_name($model);
        $serializers = array(
            'json' => function($model) use (&$options) {
                return json_encode($model);
            },
            'xml' => function($model) use (&$options) {
                trigger_error('XML Model serialization is not yet supported', E_USER_NOTICE);
            },
            'php' => function($model) use (&$options) {
                return serialize($model);
            }
        );

        $this->trigger('pre_serialize', array(&$model, $mode, $options));

        if (!isset($serializers[$mode])) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Invalid serialization mode "%s"; Avaliable modes (%s)',
                    $mode,
                    implode(',', array_keys($serializers))
                )
            );
        }

        if ($options['cache'] && isset($this->_cache[$mode][$name])) {
            // Cache allways returns the first result when a listener returns results.
            $cache = $this->trigger('serialize_cache', array(&$model, $mode, $options));
            if (isset($cache[0])) {
                return $cache[0];
            }
            return $this->_cache[$mode][$name];
        }

        $parsed = $this->_parse($model, $options);
        $return = $serializers[$mode]($parsed);

        if ($options['cache']) {
            $this->_cache[$mode][$name] = $return;
        }

        $this->trigger('post_serialize', array(
                                               $parsed,
                                               $return,
                                               &$model,
                                               $mode,
                                               $options
                                            )
                       );

        return $return;
    }

    /**
     * Parses a model object into a stdClass object for serialization.
     *
     * @param  object  $model  \prggmr\record\Model
     * @param  array  $options  Array of options to use while parsing.
     *
     * @return  object  \stdClass object
     */
    protected function _parse(record\Model $model, array $options = array()) {
        $table = $model->table();
        $cols = $table->getColumns();
        $stdClass = new \stdClass();

        if ($options['options']) {
            $stdClass->isreadonly = $model->isReadOnly();
            $stdClass->isnew      = $model->isNew();
            $stdClass->isdirty    = $model->isDirty();
        }

        $stdClass->model = \get_class($model);
        $stdClass->table = $table->getName();
        $stdClass->columns = array();

        foreach ($cols as $_name => $_column) {
            $stdClass->columns[$_name] = array(
                'name'    => $_column->getName(),
                'value'   => $_column->getValue()
            );

            if ($options['options']) {
                $stdClass->columns[$_name] += array(
                    'type'    => $_column->getType(),
                    'pk'      => $_column->isPk(),
                    'length'  => $_column->getLength(),
                    'default' => $_column->getDefault()
                );
            }
        }

        return $stdClass;
    }

    /**
     * Parses a serialized \prggmr\record\Model object, returning the parsed
     * \prggmr\record\Model object.
     *
     * @param  mixed  $str  Serialized model string.
     * @param  string  $mode  Mode to use when deserializing. Leave blank
     *         to auto-detect.
     *
     * @throws
     *
     * @return  object  \prggmr\record\Model object
     */
    public function deserialize($str, $mode = 'json') {
        $this->trigger('model.deserialize.before', array($str, $mode));
        switch ($mode) {
            case 'json':
            default:
                $decoded = json_decode($str);
                if (false === $decoded) {
                    throw new RuntimeException(
                        sprintf(
                            'Failed to parse JSON serialized model
                            due to error "%s"',
                            intval(json_last_error())
                        )
                    );
                }
                break;
            case 'php':
                $decoded = unserialize($str);
                if (false === $decoded) {
                    throw new RuntimeException(
                        'Failed to parse PHP serialized model'
                    );
                }
                break;
            case 'xml':
                trigger_error('XML Model deserialization is not yet supported',
                              E_USER_NOTICE);
                break;
        }

        if ($decoded instanceof \stdClass) {
            throw new \RuntimeException(
                'Serialized model object provided was not a valid
                serial representation, failed to generate model from source.'
            );
        }

        $this->trigger('model.deserialize.after', array($decoded, $str, $mode));

        return $decoded;
    }
}