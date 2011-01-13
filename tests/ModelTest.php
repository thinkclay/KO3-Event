<?php
include_once 'bootstrap.php';

use prggmr\record as record;

\prggmr::library('prggmr', array(
    'path'   => \prggmr::get('prggmr.config.paths.system_path').'/tests/',
    'merge'  => true
));

record\connection\Pool::instance()->add(new record\connection\adapter\MySQL('mysql:dbname=magento_test;host=127.0.0.1', 'phpmyadmin', 'newmedia'), 'My Connection', true);

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