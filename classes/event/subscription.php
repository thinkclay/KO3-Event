<?php
/**
 * The subscriber object is the main object responsible for holding our event
 * bubblers or the function which will execute when our queue says it is time.
 *
 * Though attached to a subscriptton queue the object itself contains no
 * information on what subscription it belongs to, it is possible to couple
 * it into the object if it is really needed but realistically the bubble will
 * recieve of copy of the current event which will ironically contain the
 * subscription object that this event is contained within allowing it to
 * call the event that is currently firing ... and if this seems a bit
 * crazy well thats because it is.
 */
class Subscription 
{

    /**
     * The lambda function that will execute when this subscription is triggered.
     */
    protected $_function = null;

    /**
     * String identifier for this subscription
     *
     * @var  string
     */
     protected $_identifier = null;
     

    /**
     * Constructs a new subscription object.
     *
     * @param  mixed  $function  A callable variable.
     *
     * @return  Queue
     */
    public function __construct ( $function, $identifier = null )
    {
        if (null === $identifier) $identifier = rand(100000,999999);
        $this->_function = $function;
        $this->_identifier = (string) $identifier;
    }

    /**
     * Fires this subscriptions function. Allowing for the first parameter as an array of parameters or by passing them directly.
     *
     * @param  array  $params  Array of parameters to pass.
     *
     * @throws  RuntimeException  When exception thrown within the closure.
     * @return  mixed  Results of the function
     */
    public function fire ( $params = null )
    {
        if (count(func_get_args()) >= 2) {
            $params = func_get_args();
        } else {
            // force array
            if (!is_array($params)) {
                $params = array($params);
            }
        }

        try {
            return call_user_func_array($this->_function, $params);
        } catch (Exception $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

     /**
     * Returns the identifier.
     *
     * @return  string
     */
    public function getIdentifier(/* ... */)
    {
        return $this->_identifier;
    }
}
