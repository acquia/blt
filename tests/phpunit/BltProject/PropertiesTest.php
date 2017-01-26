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
   * Tests site properties are parsed as expected.
   *
   * @group blt-project
   * @group blt-multisite
   */
  public function testSiteProperties() {

    global $argv;
    $site = $this->parseSiteNameArg();

    // Assume default site if no site argument can be parsed.
    $site = empty($site) ? 'default' : $site;

    foreach (preg_grep('/site\.[\w.]*=/', $argv) as $prop) {
      $matches = [];
      if (preg_match('/(site\.[\w.]*)="?([\w.:\/@]*)"?/', $prop, $matches)) {
        $property = $matches[1];
        $expected = $matches[2];
        $this->assertPropertyEquals($property, $expected, $site);
      }
      else {
        $this->fail("Unable to parse property string: $prop");
      }

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
    exec(
    // Run the echo property task (optionally providing a site name)
    // and parse its output.
      "vendor/bin/blt echo-property -Dproperty.name=$property " . (!empty($site) ? "-Dsite.name=$site" : "") .
      // Run command with minimal console styling.
      " -emacs -logger phing.listener.DefaultLogger", $output
    );
    // Property value will be output to the 6th line.
    $this->assertEquals($expected, $output[5], "Expected value at $property to equal $expected");
  }

  /**
   * Parses the site.name argument.
   *
   * This function will not parse multiple site.name arguments. The first
   * site.name argument found in $argv will be returned.
   *
   * @return string
   *    The site name or an empty string.
   */
  private function parseSiteNameArg() {

    global $argv;
    $re_site_name = '/site\.name="?([\w.]*)"?/';
    $site_name = preg_grep($re_site_name, $argv);

    foreach ($site_name as $name) {
      $matches = [];
      return preg_match($re_site_name, $name, $matches) ? $matches[1] : '';
    }
  }

}
