<?php
include_once 'bootstrap.php';

use prggmr\render as render;

class ViewEngineStandardTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->engine = new render\engine\Standard();
    }

    public function testAddTemplatePath()
    {
        $this->engine->path(PRGGMR_LIBRARY_PATH . '/tests/view/templates');
        $this->assertEquals(array(PRGGMR_LIBRARY_PATH . '/tests/view/templates'), $this->engine->paths());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testAddInvalidPath()
    {
        $this->engine->path('this/path/doesnt/exist');
    }

    public function testCompileTemplate()
    {
        $this->engine->path(PRGGMR_LIBRARY_PATH . '/tests/view/templates');
        $this->assertEquals('Hello World', $this->engine->compile('test.phtml', array('test_var' => 'Hello World')));
    }

    public function testCompileWithinTemplate()
    {
        $this->engine->path(PRGGMR_LIBRARY_PATH . '/tests/view/templates');
        $compile = $this->engine->compile('test_recompile.phtml', array('test_var' => 'Hello World',
                                                                        'test_var2' => 'Recompile Me'));
        $this->assertEquals('Hello World, Recompile Me', $compile);
    }

    public function testCompileNoExtension()
    {
        $this->engine->path(PRGGMR_LIBRARY_PATH . '/tests/view/templates');
        $compile = $this->engine->compile('test_recompile', array('test_var' => 'Hello World',
                                                                        'test_var2' => 'Recompile Me'));
        $this->assertEquals('Hello World, Recompile Me', $compile);
    }

    public function testEngineOptions()
    {
        $this->engine->setOpt('extension', 'pgmr');
        $this->assertNotEquals('phtml', $this->engine->getOpt('extension'));
        $this->assertEquals('pgmr', $this->engine->getOpt('extension'));
    }
}