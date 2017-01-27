<?php

namespace Acquia\Blt\Tests\BltProject;

use Acquia\Blt\Tests\BltProjectTestBase;

/**
 * Class PropertiesTest.
 *
 * Verifies that Phing properties are being parsed as expected.
 */
class PropertiesTest extends BltProjectTestBase {

  /**
   * Tests whether site-specific properties are parsed as expected.
   *
   * @group blt-project
   * @group blt-multisite
   */
  public function testSiteProperties() {
    global $site, $properties;
    if(!isset($properties)){
      $this->fail('No properties are defined.');
    }
    // Assume default site if no site name provided.
    $site_name = !isset($site) ? 'default' : $site;
    foreach ($properties as $property => $value) {
      $this->assertPropertyEquals($property, $value, $site_name);
    }
  }

  /**
   * Asserts that a given property has an expected value.
   *
   * @param string $property
   *    The property to check.
   * @param string $expected
   *    The expected value of $property.
   * @param string $site
   *    An optional site name.
   */
  protected function assertPropertyEquals($property, $expected, $site = '') {
    $output = [];
    $blt_bin = $this->projectDirectory . '/vendor/bin/blt';
    exec(
    // Run the echo property task (optionally providing a site name)
    // and parse its output.
      "$blt_bin echo-property -Dproperty.name=$property " . (!empty($site) ? "-Dsite.name=$site" : "") .
      // Run command with minimal console styling.
      " -emacs -logger phing.listener.DefaultLogger", $output
    );
    // Property value will be output to the 6th line.
    $this->assertEquals($expected, $output[5], "Expected value at $property to equal $expected");
  }

}
