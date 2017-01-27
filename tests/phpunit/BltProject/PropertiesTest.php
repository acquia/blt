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
    $this->assertArgumentsEqualProperties('site\.[\w.]+');
  }

  /**
   * Tests multisite properties are parsed as expected.
   *
   * @group blt-multisite
   */
  public function testMultisiteProperties() {
    $this->assertArgumentsEqualProperties('multisite\.[\w.]+');
  }

  /**
   * Asserts that arguments equal expected phing property values.
   *
   * Parses command line arguments and asserts they equal the values
   * provided on the command line.
   *
   * CLI usage: phpunit test/path key.property=value
   * Function usage: assertArgumentsEqualProperties(key\.[\w.]+)
   *
   * This will parse all arguments under 'key.' and assert they
   * equal the values provided on the command line (E.g., the phing
   * property 'key.property' equals 'value').
   *
   * You may also use the site.name=name argument to run the assertions
   * against a particular site.
   *
   * @param string $expression
   *    A regular expression string to parse arguments according to.
   */
  protected function assertArgumentsEqualProperties($expression) {

    global $argv;
    $site = $this->parseSiteNameArg();

    // Assume default site if no site argument can be parsed.
    $site = empty($site) ? 'default' : $site;

    $arg_matches = preg_grep('/^' . $expression . '=/', $argv);
    foreach ($arg_matches as $prop) {
      $matches = [];
      if (preg_match('/^(' . $expression . ')="?([\w.:\/@,]+)"?/', $prop, $matches)) {
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
    $re_site_name = '/site\.name="?([\w.]+)"?/';
    $site_name = preg_grep($re_site_name, $argv);

    foreach ($site_name as $name) {
      $matches = [];
      return preg_match($re_site_name, $name, $matches) ? $matches[1] : '';
    }
  }

}
