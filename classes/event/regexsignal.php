<?php defined('SYSPATH') or die('No direct script access.');

/**
 * The default signal object allows for signals of any type requiring only
 * that they evalute to true on a strict comparison check, otherwise meaning
 * each signal must be exactly equal both in type and value.
 */
class RegexSignal extends Signal 
{

    /**
     * Constructs a regular expression signal. Support for :name parameters are supported.
     *
     * @param  string  $signal  Regex event signal
     * @param  mixed  $chain  An additional signal for a chain
     *
     * @return  void
     */
    public function __construct ( $signal, $chain = null )
    {
        $regex = preg_replace('#:([\w]+)#i', 'fix\(?P<$1>[\w_-]+fix\)', $signal);
        $regex = str_replace('fix\(', '(', $regex);
        $regex = str_replace('fix\)', ')', $regex);
        $regex = '#' . $regex . '$#i';
        parent::__construct($regex, $chain);
    }

    /**
     * Compares the event signal given with itself using regular expressions.
     *
     * @param  mixed  $signal  Signal to compare
     *
     * @return  mixed  False on failure. True if matches. String/Array
     *          return results found via the match.
     */
    public function compare ( $signal )
    {
        if (preg_match($this->_signal, $signal, $matches)) 
        {
            array_shift($matches);
            if (count($matches) != 0) 
            {
                foreach ($matches as $_k => $_v) {
                    if (is_string($_k))
                        unset($matches[$_k]);
                }
                return $matches;
            }
            return true;
        }
        return false;
    }
}