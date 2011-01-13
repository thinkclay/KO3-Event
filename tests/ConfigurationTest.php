<?php

include_once 'bootstrap.php';

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    public function testConfig()
    {
        $config = \prggmr::get('prggmr.config');
        $this->assertArrayHasKey('mysql', $config);
        $this->assertArrayHasKey('system', $config);
        $this->assertArrayHasKey('paths', $config);
        $this->assertArrayHasKey('files', $config);
    }

    public function testLibraryLoader()
    {
        \prggmr::library('prggmr.phpunit', array(
            'path'   => \prggmr::get('prggmr.config.paths.system_path'),
        ));
        \prggmr::library('prggmr.phpunit', array(
            'path'   => \prggmr::get('prggmr.config.paths.system_path').'/lib/',
            'merge'  => true
        ));
        $library = \prggmr::registry('object')->__libraries['prggmr.phpunit'];
        $this->assertType('array', $library['path']);
        $this->assertEquals(2, count($library['path']));
    }
}
