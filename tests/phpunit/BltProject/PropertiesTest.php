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
   * @group blt-multisite
   */
  public function testSitePropertiesEqual() {
    global $_blt_site, $_blt_properties;
    if (!isset($_blt_properties)) {
      $this->fail('No properties are defined.');
    }
    // Assume default site if no site name provided.
    $site_name = !isset($_blt_site) ? 'default' : $_blt_site;
    foreach ($_blt_properties as $property => $value) {
      $this->assertPropertyEquals($property, $value, $site_name);
    }
  }

  /**
   * Tests whether site-specific properties are not set (as expected).
   *
   * @group blt-multisite
   */
  public function testSitePropertiesNotSet() {
    global $_blt_site;
    // Assume default site if no site name provided.
    $site_name = !isset($_blt_site) ? 'default' : $_blt_site;
    // Create some dummy properties and assert they can't be found.
    foreach (range(0, 10) as $num) {
      $this->assertPropertyEmpty(uniqid(), $site_name);
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
    $value = $this->getProperty($property, $site);
    $this->assertEquals($expected, $value,
      "Expected value at $property to equal $expected. Instead, $property equals $value.");
  }

  /**
   * Asserts that a given property is empty.
   *
   * @param string $property
   *    The property to check.
   * @param string $site
   *    An optional site name.
   */
  protected function assertPropertyEmpty($property, $site = '') {
    $value = $this->getProperty($property, $site);
    $this->assertEmpty($value,
      "Expected value at $property to be empty. Instead, $property equals $value.");
  }

  /**
   * Gets the value a given property (optionally specifying a site).
   *
   * @param string $property
   *    The property to check.
   * @param string $site
   *    An optional site name.
   *
   * @return string
   *    The value of $property.
   */
  private function getProperty($property, $site = '') {
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
    return $output[5];
  }

}
