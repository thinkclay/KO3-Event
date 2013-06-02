<?php defined('SYSPATH') or die('No direct script access.');

class Kohana_Event extends Event_Core
{
    /**
     * The storage of Queues.
     *
     * @var  object  SplObjectStorage
     */
    private $_storage = null;

    /**
     * Construction inits our blank object storage, nothing else.
     *
     * @return  void
     */
    public function __construct ()
    {
        $this->_storage = new SplObjectStorage();
    }

    /**
     * Attaches a new subscription to a signal queue.
     *
     * NOTE: Passing an array as the signal parameter should be done only once per subscription que as each time a new Queue is created.
     *
     *
     * @param   mixed  $signal       Signal the subscription will attach to, this can be an object, string, or array for a chained signal.
     * @param   mixed  $callback     Callback closure that will trigger on fire or a Callback object.
     * @param   mixed  $identifier   String identifier for this subscription, if an integer is provided it will be treated as the priority.
     * @param   mixed  $priority     Priority of this subscription within the Queue / Chain
     *
     * @throws  InvalidArgumentException  Thrown when an invalid callback is provided.
     * @return  void
     */
    public function listen ( $signal, $callback, $identifier = null, $priority = null )
    {
        if ( is_int($identifier) )
            $priority = $identifier;

        if ( ! $callback instanceof Event_Callback)
        {
            if ( ! is_callable($callback) )
                throw new InvalidArgumentException('subscription callback is not a valid callback');

            $callback = new Event_Callback($callback, $identifier);
        }

        if ( is_array($signal) AND isset($signal[0]) AND isset($signal[1]) )
        {
            $queue = $this->queue($signal[0]);
            $chain = $this->queue($signal[1]);
            $queue->get_signal()->set_chain($signal[1]);
            return $queue->enqueue($subscription, $priority);
        }
        else
        {
            return $this->queue($signal)->enqueue($callback, $priority);
        }
    }

    /**
     * Removes a callback from the queue.
     *
     * @param   mixed  $signal    Signal the subscription is attached to, this can be a Signal object or the signal representation.
     *
     * @param   mixed  $callback  String identifier of the subscription or a Subscription object.
     *
     * @throws  InvalidArgumentException
     * @return  void
     */
    public static function dequeue ( $signal, $callback )
    {
        $queue = $this->queue($signal, false);

        if (false === $queue)
            return false;

        return $queue->dequeue($callback);
    }

    /**
     * Locates a Queue object in storage, if not found one is created.
     *
     * @param   mixed    $signal     Signal the queue represents.
     * @param   boolean  $generate   Generate the queue if not found.
     *
     * @return  mixed  Queue object, false if generate is false and queue is not found.
     */
    public function queue ( $signal, $generate = true )
    {
        $obj = (is_object($signal) AND $signal instanceof Event_Signal);

        $this->_storage->rewind();
        while ( $this->_storage->valid() )
        {
            $storage = ($this->_storage->current()->get_signal() === $signal);

            if ( ($obj AND $storage) OR ($this->_storage->current()->get_signal(true) === $signal) )
                return $this->_storage->current();

            $this->_storage->next();
        }

        if ( ! $generate )
            return false;

        if ( ! $obj )
            $signal = new Event_Signal($signal);

        $obj = new Event_Queue($signal);

        // new queue
        $this->_storage->attach($obj);
        return $obj;
    }

    /**
     * Fires an event signal.
     *
     * @param  mixed  $signal  The event signal, this can be the signal object or the signal representation.
     * @param  array  $vars  Array of variables to pass the subscribers
     * @param  object  $event  Event
     *
     * @return  object  Event
     */
    public function fire ( $signal, $vars = null, $event = null )
    {
        // Lazy load models for any executing events
        $this->lazyload();

        $compare = false;
        $this->_storage->rewind();

        while ( $this->_storage->valid() )
        {
            // compare the signal given with the queue signal ..
            // TODO: Currently this allows for the first signal match to be used
            // this should allow for either it to continue on with itself until
            // it finds the signal it wants based on some crazy algorithm that
            // has yet to be written OR use every signal it compares with
            if ( ($compare = $this->_storage->current()->get_signal()->compare($signal)) !== false )
                break;

            $this->_storage->next();
        }

        if ( $compare === false )
            return false;

        if ( $vars !== null )
        {
            if ( ! is_array($vars) )
                $vars = array($vars);
        }

        $queue = $this->_storage->current();
        // rewinds and prioritizes the queue
        $queue->rewind();

        if ( ! is_object($event) )
        {
            $event = new Event_Instance($queue->get_signal());
        }
        elseif ( ! $event instanceof Event_Instance )
        {
            throw new InvalidArgumentException(
                sprintf('fire expected instance of Event recieved "%s"', get_class($event))
            );
        }

        $event->set_signal($queue->get_signal());
        $event->set_state(Event_Instance::STATE_ACTIVE);

        if ( count($vars) === 0 )
            $vars = array(&$event);

        else
            $vars = array_merge(array(&$event), $vars);

        if ( $compare !== true )
        {
            // allow for array return
            if ( is_array($compare) )
                $vars = array_merge($vars, $compare);

            else
                $vars[] = $compare;
        }

        // the main loop
        while ( $queue->valid() )
        {
            if ($event->is_halted())
                break;

            $queue->current()->fire($vars);

            if ( $event->get_state() == Event_Instance::STATE_ERROR )
            {
                throw new RuntimeException(
                    sprintf('Event execution failed with message "%s"', $event->getStateMessage())
                );
            }
            $queue->next();
        }

        // the chain
        if ( ($chain = $queue->get_signal()->get_chain()) !== null )
        {
            if ( ($data = $event->getData()) !== null )
            {
                // remove the current event from the vars
                unset($vars[0]);
                $vars = array_merge($vars, $event->get_data());
            }

            $chain = $this->fire($chain, $vars);

            if ( $chain )
                $event->set_chain($chain);
        }

        // keep the event in an active state until its chain completes
        $event->set_state(Event_Instance::STATE_INACTIVE);

        return $event;
    }

    /**
     * Flushes the engine.
     */
    public function flush ()
    {
        $this->_storage = new SplObjectStorage();
    }

    /**
     * Returns the count of subsciption queues in the engine.
     *
     * @return  integer
     */
    public function count ()
    {
        return $this->_storage->count();
    }

    /**
     * Assembles the string identifier, builds from the current request if no args are passed
     *
     * @return  string
     */
    public static function assemble ( $namespace = null, $controller = null, $action = null )
    {
        if ( ! $namespace )
            $namespace = Request::$current->directory();

        if ( ! $controller )
            $controller = Request::$current->controller();

        if ( ! $action )
            $action = Request::$current->action();

        return strtoupper($namespace.'_'.$controller.'_'.$action);
    }

    /**
     * Lazy load models based on the currently firing event
     *
     * @return  string
     */
    public static function lazyload ()
    {
        $controller = Request::$current->controller();
        $action = Request::$current->action();

        $current_executing_event = 'Model_Event_'.$controller;
        if ( class_exists($current_executing_event) )
            $current_executing_event::$action();
    }
}