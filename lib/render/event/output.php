<?php
namespace prggmr\render\event;
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
 * @category  Web
 * @copyright  Copyright (c), 2010 Nickolas Whiting
 */

/************************************************************
 * Renderer Event Output
 * 
 * Handles the outputable data.
 */
class Output {
    
    #/**
    # * Data ready for output via a renderer processor.
    # *
    # * @var  object  Object of data stored in "key" -> "value" format.
    # *       Data is processed at the end of execution and dumped.
    # */
    #protected $_data = null;
    
    /**
     * Array of processors for the data.
     *
     * @var  array  Array containing anonymous function used for processing
     *       data prior to output.
     */
    protected $_processors = array();
    
    /**
     * JSON PROCESSOR
     */
    const JSON_PRC = 'json';
    
    /**
     * XML PROCESSOR
     *
     * @todo  Setup the XML processor.
     */
    const XML_PRC = 'xml'; 
    
    
    /**
     * Attaches a variable/s to the _data array.
     *
     * @param  mixed  $var  Variable name or array of vars to attach.
     * @param  mixed  $val  Value if array not provided as $var.
     *
     * @return  boolean
     */
    public function attach($var, $val = null) {
        if (is_array($var)) {
            $self = $this;
            array_walk($var, function($v, $k) use($self) {
                $self->attach($k, $v);
            });
            return true;
        }
        $this->$var = $val;
        return true;
    }
    
    /**
     * Constructor sets up the JSON and XML processor.
     */
    public function __construct()
    {
        #$this->_data = new \stdClass();
        $this->_processors[self::JSON_PRC] = function($data, $options = array()) {
            /**
             * Always output a result in JSON.
             * 
             * @todo Should this be moved to maybe the processor call
             */
            if (!isset($data->result)) {
                $data->result = true;
            }
            try {
                $json = json_encode($data);
            } catch(LogicException $e) {
                try {
                    $std  = new \stdClass();
                    $std->result = false;
                    $json = json_encode($std);
                } catch (LogicException $e) {
                    return '
                    {"result": false,
                     "error": "JSON Processor failed due to
                               an interal json_encode error."
                     }';
                }
                return $json;
            }
            echo '<pre>';
            return $json;
        };
        $this->_processors[self::JSON_PRC] = function($data, $options = array()) {
            /**
             * Always output a result in JSON.
             * 
             * @todo Should this be moved to maybe the processor call
             */
            if (!isset($data->result)) {
                $data->result = true;
            }
            try {
                $json = json_encode($data);
            } catch(LogicException $e) {
                try {
                    $std  = new \stdClass();
                    $std->result = false;
                    $json = json_encode($std);
                } catch (LogicException $e) {
                    return '
                    {"result": false,
                     "error": "JSON Processor failed due to
                               an interal json_encode error."
                     }';
                }
                return $json;
            }
            echo '<pre>';
            return $json;
        };
        return true;
    }
    
    /**
     * Calls the given processor and return printable string.
     * Triggers the renderer_output event.
     *
     * @param  string  $processor  Name of the processor to use for the output.
     * @param  array  $options  Array of options to pass along to the processor.
     *
     * @return  boolean
     */
    public function print_d($processor, $options = array())
    {
        if (!isset($this->_processors[$processor])) {
            throw new InvalidArgumentException(
                sprinf(
                    'Renderer_Event_Output (%s) processor does not exist',
                    $processor
                )
            );
        }
        $data = \prggmr::trigger('renderer_output', array(
            $this->_data), array(
                'namespace' => 'prggmr')
        );
        $temp = array();
        if (is_array($data)) {
            for ($i=0;$i!=count($data);$i++){
                $temp += $data;
            }
        }
        $return = (object) array_merge((array) $this->_data, $temp);
        unset($return['processors']);
        return $this->_processors[$processor]($return);
    }
    
    /**
     * Generates a renderable template, the template is parsed
     * using the renderer's current object scope so $this->var is
     * accesible.
     *
     * @param  string  $template  Template file that will be parsed
     * 
     * @return  string  Parsed template string
     */
    public function render($template)
    {
        ob_start();
            $paths = \prggmr::load($template, array('return_path' => true));
            foreach ($paths as $k => $v) {
                if (file_exists($v)) {
                    include $v;
                    break;
                }
            }
            $file = ob_get_clean();
            return $file;
		ob_flush();
    }
    
}