<?php

namespace Acquia\Blt\Tests\BltProject;


use Acquia\Blt\Tests\BltProjectTestBase;
use Symfony\Component\Yaml\Yaml;

/**
 * Class DrushTest.
 *
 * Verifies that Phing properties are being parsed as expected.
 */
class PropertiesTest extends BltProjectTestBase {

    /**
     * Tests that Phing parses property files as expected.
     *
     * @group blt-project
     */
    public function testparseProperties($file = '', $expected = array()) {

        if(!empty($file)){
            // @todo allow an arbitrary file to be provided.
        }
        else {
            // Create a file to test against.
            $data = array(
                'aliases' => array('phpunit' => '127.0.0.1:8888'),
                'uri'  => array('phpunit' => 'site.ci.com'),
            );

            $expected = array(
                'aliases.phpunit' => '127.0.0.1:8888',
                'uri.phpunit' => 'site.ci.com',
            );

            $file = sys_get_temp_dir() . uniqid('blt-') . '.yml';
            file_put_contents($file, Yaml::dump($data));

        }

        foreach($expected as $key => $value) {
            $output = [];
            // Run command with minimal console styling.
            exec("vendor/bin/blt echo-property -Dproperty.name=\"$key\" -propertyfile $file -e -logger phing.listener.DefaultLogger", $output);
            $this->assertEquals($value, $output[5], "Expected value at $key to equal $value");
        }

    }

}
