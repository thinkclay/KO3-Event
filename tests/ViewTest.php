<?php
include_once 'bootstrap.php';

use prggmr\render as render;

class ViewTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->view = new render\View();
    }

    public function tearDown()
    {
        unset($this->view);
    }

    public function testViewConstructor()
    {
        $obj = new render\engine\Standard();
        $engines = $this->view->getEngines();
        $this->assertArrayHasKey('Standard', $engines);
        $this->assertArrayHasKey('object', $engines['Standard']);
        $this->assertArrayHasKey('default', $engines['Standard']);
        $this->assertEquals(true, $engines['Standard']['default']);
        $this->assertEquals('Standard', get_class_name($engines['Standard']['object']));
    }

    public function testVarAssignments()
    {
        $this->view->assign('test_key', true);
        $this->assertEquals(array('test_key' => true), $this->view->getTemplateVars());
        $engines = $this->view->getEngines();
        $this->assertObjectHasAttribute('test_key', $engines['Standard']['object']);
        $this->assertEquals(true, $engines['Standard']['object']->test_key);
        $this->view->assign('test_key', 'Overwrite Val');
        $this->assertEquals(array('test_key' => 'Overwrite Val'), $this->view->getTemplateVars());
        $this->assertEquals('Overwrite Val', $engines['Standard']['object']->test_key);
    }

    public function testVarAssignmentsOverloading()
    {
        $this->view->test = 'Test Val';
        $this->view->test_key = 'Overwrite Val';
        $engines = $this->view->getEngines();
        $this->assertEquals(array('test_key' => 'Overwrite Val', 'test' => 'Test Val'), $this->view->getTemplateVars());
        $this->assertEquals('Test Val', $engines['Standard']['object']->test);
    }

    public function testVarAssignmentsEventManipulation()
    {
        prggmr::listen('var_assign', function($key, $value, $view) {
            $view->assign($key, $value . ' Added In Event', array('event' => false));
        }, array('namespace' => 'view'));

        $this->view->assign('test_val_2', 'This has been');
        $engines = $this->view->getEngines();
        $this->assertEquals(array('test_val_2' => 'This has been Added In Event'), $this->view->getTemplateVars());
        $this->assertEquals('This has been Added In Event', $engines['Standard']['object']->test_val_2);
    }
}