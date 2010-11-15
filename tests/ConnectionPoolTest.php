<?php
include_once '../system/bootstrap.php';

use prggmr\record\connection as record;

class ConnectionPoolTest extends \PHPUnit_Framework_TestCase
{
    public function setup()
    {
        $this->pool = &record\Pool::instance();
    }
    
    public function testAdd()
    {
        $this->pool->add(
            new record\adapter\MySQL('mysql:dbname=test;host=127.0.0.1', 'root', '')
        );
        $this->assertTrue($this->pool->exists('MySQL'));
        $this->assertTrue($this->pool->getConnection('MySQL')->isDefault());
    }
    
    public function testAddTwo()
    {
        $this->pool->add(
            new record\adapter\MySQL('mysql:dbname=test;host=127.0.0.1', 'root', ''), 'MySQL2'
        );
        
        $this->assertTrue($this->pool->exists('MySQL2'));
        $this->assertEquals(2, count($this->pool->listConnections()));
    }
    
    public function testDefault()
    {
        #$this->assertTrue($this->pool->getConnection('MySQL')->isDefault());
        $this->pool->setDefault('MySQL2');
        $this->assertFalse($this->pool->getConnection('MySQL')->isDefault());
        $this->assertTrue($this->pool->getConnection('MySQL2')->isDefault());
    }
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testUnknownConnection()
    {
        $this->pool->getConnection('Unknown');
    }
}