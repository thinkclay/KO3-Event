<?php
/**
 * The queue object is a priority queue implemented using a heap, it was decided
 * aganist using PHP's implementation of the current PriorityQueue which is not
 * to say it isn't useful, only wasteful. This does come at a disadvantage of
 * sacrificing performance over functionality ... even at a small cost.
 *
 * The priority works as a min-heap, which also bring the point that unlike
 * the implementation in the SPL priority is limited only to integers this is
 * done for performance reasons since sorting integers will be faster than
 * any other method.
 *
 * The heap is implemented using only the priority, the data is ignored.
 *
 * The object itself represents the queue of subscriptions for an event Signal,
 * which is passed as the constructors first parameter.
 */
class Queue extends SplObjectStorage {

    /**
     * The event signal for which this queue is attaching subscriptions.
     *
     * @var  object  Signal
     */
    protected $_signal = null;

    /**
     * Flag for the prioritizing the queue.
     *
     * @var  boolean
     */
    public $dirty = false;

    /**
     * Constructs a new queue object.
     *
     * @param  object  $signal  Signal
     *
     * @return  Queue
     */
    public function __construct(Signal $signal)
    {
        $this->_signal = $signal;
    }

    /**
     * Returns the event signal this queue represents.
     *
     * @param  boolean  $signal  True returns the signal rather than the object
     *
     * @return  object
     */
    public function getSignal($signal = false)
    {
        if (!$signal) {
            return $this->_signal;
        } else {
            return $this->_signal->signal();
        }
    }

    /**
     * Inserts a subscription into the queue.
     *
     * @param  object  $subscription  Subscription
     * @param  integer $priority  Priority of the subscription
     *
     * @return  void
     */
    public function enqueue ( Subscription $subscription, $priority = 100 )
    {
        $this->dirty = true;
        if (null === $priority) $priority = 100;
        $priority = (integer) $priority;
        parent::attach($subscription, $priority);
    }

    /**
    * Removes a subscription from the queue.
    *
    * @param  mixed  subscription  String identifier of the subscription or a Subscription object.
    *
    * @throws  InvalidArgumentException
    * @return  void
    */
    public function dequeue ( $subscription )
    {
        if (is_string($subscription) && $this->locate($subscription)) 
        {
            parent::detach($this->current());
            $this->dirty = true;
        } 
        elseif ($subscription instanceof Subscription) 
        {
            parent::detach($subscription);
            $this->dirty = true;
        }
    }

    /**
    * Locates a subscription in the queue by the identifier setting as the current.
    *
    * @param  string  $identifier  String identifier of the subscription
    *
    * @return  void
    */
    public function locate ( $identifier )
    {
        $this->rewind(false);
        while($this->valid()) 
        {
            if ($this->current()->getIdentifier() == $identifier) 
            {
                return true;
            }
            $this->next();
        }
        return false;
    }

    /**
     * Rewinds the iterator to prepare for iteration of the queue, also triggers prioritizing.
     *
     * @param  boolean  $prioritize  Flag to prioritize the queue.
     *
     * @return  void
     */
    public function rewind ( $prioritize = true )
    {
        if ($prioritize) {
            $this->_prioritize();
        }
        return parent::rewind();
    }

    /**
     * Prioritizes the queue.
     *
     * @return  void
     */
    protected function _prioritize ( )
    {
        if (!$this->dirty) return null;
        $tmp = array();
        $this->rewind(false);
        while($this->valid()) 
        {
            $pri = $this->getInfo();
            if (!isset($tmp[$pri])) {
                $tmp[$pri] = array();
            }
            $tmp[$pri][] = $this->current();
            $this->next();
        }
		
        ksort($tmp, SORT_NUMERIC);
        $this->removeAll($this);
        foreach ($tmp as $priority => $_array) 
        {
            foreach ($_array as $_sub) {
                parent::attach($_sub, $priority);
            }
        }
        $this->dirty = false;
    }

    // public static function attach()
    // {
        // throw new Exception('attach method disallowed; use of enqueue required');
    // }

    // public function detach()
    // {
        // throw new Exception('detach method disallowed; use of dequeue required');
    // }

    /**
     * Flushes the queue.
     *
     * @return  void
     */
    public function flush(/* ... */)
    {
        $this->removeAll($this);
    }
}