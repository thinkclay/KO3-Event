<?php defined('SYSPATH') OR die('No direct access allowed.');

class Model_Event extends Model
{
    /**
     * Use late static binding to avoid errors, and can be used to create a fallback solution
     * since no static method was found within the scope, this helps debug events as well as
     * creates a hook for an alternative search (like configs, cascading, etc)
     */
    public static function __callStatic ($name, $arguments)
    {
        error_log('------------');
        error_log('No event found, executing call static');
        error_log('Name: '.$name);
        foreach ($arguments as $arg)
        {
            error_log('arg: '.$arg);
        }
        error_log('------------');
    }
}
