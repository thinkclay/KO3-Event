<?php
/**
 * The default signal object allows for signals of any type requiring only
 * that they evalute to true on a strict comparison check, otherwise meaning
 * each signal must be exactly equal both in type and value.
 */
class Signal {

    /**
     * The event signal.
     *
     * @var  string
     */
    protected $_signal = null;

    /**
     * Chain signal
     *
     * @var  mixed
     */
    protected $_chain = null;

    /**
     * Constructs a new signal object.
     *
     * @param  mixed  $signal  Event signal
     * @param  mixed  $chain  An additional signal for a chain
     *
     * @return  \prggmr\Queue
     */
    public function __construct($signal, $chain = null)
    {
        if (is_object($signal)) $signal = spl_object_hash($signal);
        $this->_chain = $chain;
        $this->_signal = $signal;
    }

    /**
     * Compares the event signal given with itself.
     *
     * @param  mixed  $signal  Signal to compare
     *
     * @return  mixed  False on failure. True if matches. String/Array
     *          return results found via the match.
     */
    public function compare($signal)
    {
        if (is_object($signal)) $signal = spl_object_hash($signal);
        return ($this->_signal === $signal);
    }

    /**
     * Returns the signal.
     *
     * @return  mixed  Event signal.
     */
    public function signal(/* ... */)
    {
        return $this->_signal;
    }

    /**
     * Returns the signal chain.
     *
     * @return  mixed
     */
    public function getChain(/* ... */)
    {
        return $this->_chain;
    }

    /**
     * Sets the signal chain
     *
     * @param  mixed  $signal  Chain signal
     *
     * @return  void
     */
    public function setChain($signal)
    {
        $this->_chain = $signal;
    }
}