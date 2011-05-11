<?php
namespace prggmr;
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
 * @package  prggmr
 * @copyright  Copyright (c), 2010 Nickolas Whiting
 */


class Benchmark extends Data {

    /**
     * Provides runtime statistical data.
     *
     * @var  array  Array of runtime statistical information.
     */
    public static $__stats = array(
        'events'     => array(),
        'benchmarks' => array()
    );

    /**
	* Benchmarks current system runtime useage information for debugging
	* purposes.
	*
    * @param  string  $op  start - Begin benchmark, stop - End Benchmark
	*
	* @param  string  $name  Name of benchmark
	*
	* @return  mixed  Array of info on stop, boolean on start
	*/
    public static function benchmark($op, $name)
    {
        $microtime = function() {
            $time = explode(" ",microtime());
            return $time[0] + $time[1];
        };

        $memory = function() {
            $pid = getmypid();
            exec("ps -o rss -p $pid", $output);
            return $output[1] * 1024;
        };
        switch ($op) {
            case 'start':
                $stats = array(
                    'memory' => $memory(),
                    'time'   => $microtime()
                );
                static::set('prggmr.stats.benchmark.'.$name, $stats);
				return true;
                break;
            case 'stop':
                $data = array(
                              'memory' => $memory(),
                              'time'   => $microtime(),
                              'start'  => 0,
                              'end'    => time()
                              );
                $stats = static::get('prggmr.stats.benchmark.'.$name);
                if ($stats != false) {
                    $data['memory'] = ($stats['memory'] > $data['memory'])
					? $stats['memory'] - $data['memory'] :
					$data['memory'] - $stats['memory'];
                    $data['time'] = $data['time'] - $stats['time'];
                    $data['start'] = $stats;
                    $data['memory_total'] = static::formatBytes($data['memory'], 2);
                }
                static::set('prggmr.stats.benchmark.'.$name, $data);
                static::$__stats['benchmarks'][$name] = $data;
                return $data;
                break;
			default:
				return null;
			break;
        }

		return null;
    }

    public static function formatBytes($size, $precision = 2)
    {
        $base = log($size) / log(1024);
        $suffixes = array('', 'k', 'M', 'G', 'T');

        return round(pow(1024, $base - floor($base)), $precision) . $suffixes[floor($base)];
    }

}