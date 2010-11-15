<?php
include '../system/bootstrap.php';

use prggmr\record as record;


class ConnectionPoolTest extends PHPUnit_Framework_TestCase
{
    /**
     * @depends ConnectionInstanceTest
     */
    public function setup()
    {
        $this->pool = new record\Pool();
        $this->assertInstanceOf(record\Pool, $this->pool);
    }
    
    public function testAddMySQL()
    {
        $this->pool->add(
            record\adapter('mysql:dbname=test;host=127.0.0.1', 'root', '')
        );
    }
}