<?php
include_once 'bootstrap.php';

use prggmr\record as record;

\prggmr::library('prggmr', array(
    'path'   => PRGGMR_LIBRARY_PATH.'/tests/',
    'merge'  => true
));

class ModelTest extends \PHPUnit_Framework_TestCase
{
    public function __construct()
    {

    }

    public function testOne()
    {
        $cars = new models\Cars_Model();
        $cars->name = 'One Fucking Thousand';
        $cars->number = 1500;
        $cars->save();
    }

    public function testTwo()
    {

    }
}