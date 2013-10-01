<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * The callback object is the main object responsible for holding our event
 * bubblers or the function which will execute when our queue says it is time.
 *
 * Though attached to a queue the object itself contains no
 * information on what listener it belongs to, it is possible to couple
 * it into the object if it is really needed but realistically the bubble will
 * recieve of copy of the current event which will ironically contain the
 * listener object that this event is contained within allowing it to
 * call the event that is currently firing ... and if this seems a bit
 * crazy well thats because it is.
 */
class Kohana_Event_Callback
{
    /**
     * The lambda function that will execute when this callback is triggered.
     */
    protected $_function = NULL;

    /**
     * String identifier for this callback
     *
     * @var  string
     */
     protected $_identifier = NULL;


    /**
     * Constructs a new callback object.
     *
     * @param  mixed  $function  A callable variable.
     *
     * @return  Queue
     */
    public function __construct($function, $identifier = NULL)
    {
        if ($identifier === NULL)
        {
            $identifier = rand(100000,999999);
        }

        $this->_function = $function;
        $this->_identifier = (string) $identifier;
    }

    /**
     * Fires this callback function. Allowing for the first parameter as an array of parameters or by passing them directly.
     *
     * @param  array  $params  Array of parameters to pass.
     *
     * @throws  RuntimeException  When exception thrown within the closure.
     * @return  mixed  Results of the function
     */
    public function fire($params = NULL)
    {
        if (count(func_get_args()) >= 2)
        {
            $params = func_get_args();
        }
        elseif ( ! is_array($params))
        {
            $params = array($params);
        }

        try
        {
            return call_user_func_array($this->_function, $params);
        }
        catch (Exception $e)
        {
            throw new RuntimeException($e->getMessage());
        }
    }

    /**
     * Returns the identifier.
     *
     * @return  string
     */
    public function get_identifier()
    {
        return $this->_identifier;
    }
}