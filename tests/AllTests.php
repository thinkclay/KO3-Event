<?php

/**
 * @credit kla/php-activerecord
 */

foreach (glob('*Test.php') as $file)
{
    include($file);
}

class AllTests
{
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('PHPUnit');
        
        foreach (glob('*Test.php') as $file)
        {
            $suite->addTestSuite(substr($file,0,-4));
        }
    
        return $suite;
    }
}