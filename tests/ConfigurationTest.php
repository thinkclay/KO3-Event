<?php

include_once 'bootstrap.php';

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    public function testLibraryLoader()
    {
        $cwd = getcwd().'/';
        \prggmr::library('prggmr.phpunit', array(
            'path'   => $cwd,
        ));
        \prggmr::library('prggmr.phpunit', array(
            'path'   => $cwd.'../lib/',
            'merge'  => true
        ));
        $library = \prggmr::registry('object')->__libraries['prggmr.phpunit'];
        $this->assertType('array', $library['path']);
        $this->assertEquals(2, count($library['path']));
    }
}
