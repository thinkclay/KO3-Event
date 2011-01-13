<?php
include_once 'bootstrap.php';

use prggmr\record\connection as record;

class ConnectionInstanceTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->config = \prggmr::get('mysql');
        $this->instance = new record\adapter\MySQL($this->config['dsn'], $this->config['username'], $this->config['password']);
    }

    public function testProperties()
    {
        $this->assertEquals($this->config['user'], $this->instance->user);
        $this->assertEquals($this->config['pass'], $this->instance->password);
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