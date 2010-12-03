<?php
include_once 'bootstrap.php';

use prggmr\render as render;

class ViewEngineStandardTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->engine = new render\engine\Standard();
        $this->sys_path = \prggmr::get('prggmr.config.paths.system_path');
    }
    
    public function testAddTemplatePath()
    {
        $this->engine->path($this->sys_path . '/tests/view/templates');
        $this->assertEquals(array($this->sys_path . '/tests/view/templates'), $this->engine->paths());
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
        $this->engine->path($this->sys_path . '/tests/view/templates');
        $this->assertEquals('Hello World', $this->engine->compile('test.phtml', array('test_var' => 'Hello World')));   
    }
    
    public function testCompileWithinTemplate()
    {
        $this->engine->path($this->sys_path . '/tests/view/templates');
        $compile = $this->engine->compile('test_recompile.phtml', array('test_var' => 'Hello World',
                                                                        'test_var2' => 'Recompile Me'));
        $this->assertEquals('string', gettype($compile));   
    }
}