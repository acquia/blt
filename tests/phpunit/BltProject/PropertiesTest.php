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
   * Tests whether multisite.name is parsed as expected.
   *
   * @group blt-multisite
   */
  public function testMultisiteProperties() {
    $this->assertPropertyEquals('multisite.name', BLT_MULTISITE_NAME);
  }

  /**
   * Asserts that a given property has an expected value.
   *
   * @param string $property
   *   The property to check.
   * @param string $expected
   *   The expected value of $property.
   * @param string $site
   *   An optional site name.
   */
  protected function assertPropertyEquals($property, $expected, $site = '') {
    $value = $this->getProperty($property, $site);
    $this->assertEquals($expected, $value,
      "Expected value at $property to equal '$expected'. Instead, $property equals '$value'.");
  }

  /**
   * Gets the value a given property (optionally specifying a site).
   *
   * @param string $property
   *   The property to check.
   * @param string $site
   *   An optional site name.
   *
   * @return string
   *   The value of $property.
   */
  private function getProperty($property, $site = '') {
    $output = [];
    $blt_bin = $this->projectDirectory . '/vendor/bin/blt';
    exec(
    // Run the echo-property task (optionally providing a site name)
    // and parse its output.
      "$blt_bin echo-property -Dproperty.name=$property " . (!empty($site) ? "-Dmultisite.name=$site" : "") .
      // Run command with minimal output and console styling.
      " -emacs -silent", $output
    );
    // Property value will be output to the 1st line.
    // Return an empty string if $property does not exist.
    return !empty($output[0]) ? $output[0] : '';
  }

}
