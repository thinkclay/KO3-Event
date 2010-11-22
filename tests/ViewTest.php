<?php
include_once 'bootstrap.php';

use prggmr\render as render;

class ViewTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->view = new render\View();
    }
    
    public function testViewConstructor()
    {
        $this->assertEquals(array(0=>'Standard'), $this->view->getEngines());
    }
    
    public function testVarAssignments()
    {
    }
}