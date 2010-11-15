<?php
include_once '../system/bootstrap.php';

use prggmr\record\connection as record;

class ConnectionInstanceTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->instance = new record\adapter\MySQL('mysql:dbname=test;host=127.0.0.1', 'root', '');
    }
    
    public function testProperties()
    {
        $this->assertEquals('root', $this->instance->user);
        $this->assertEquals('', $this->instance->password);
        $this->assertEquals($this->instance->getDefaultPort(), $this->instance->port);
    }
    
    public function testDefault()
    {
        $this->assertFalse($this->instance->isDefault());
        $this->instance->isDefault(true);
        $this->assertTrue($this->instance->isDefault());
        $this->instance->isDefault(false);
        $this->assertFalse($this->instance->isDefault());
    }
    
    public function testQuoteFull()
    {
        $this->assertEquals('`testvar`', $this->instance->quote('testvar'));
    }
    
    public function testQuotePrepend()
    {
        $this->assertEquals('`testvar`', $this->instance->quote('testvar`'));
    }
    
    public function testQuoteAppend()
    {
        $this->assertEquals('`testvar`', $this->instance->quote('`testvar'));
    }
    
    public function testQuoteNone()
    {
        $this->assertEquals('`testvar`', $this->instance->quote('`testvar`'));
    }
}