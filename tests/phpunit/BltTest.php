<?php

namespace Drupal;

use Symfony\Component\Yaml\Yaml;

/**
 * Class BLTTest.
 *
 * Verifies that project structure and configuration matches BLT
 * standards.
 */
class BltTest extends \PHPUnit_Framework_TestCase {

  /**
   * Class constructor.
   */
  public function __construct() {
    $this->projectDirectory = realpath(dirname(__FILE__) . '/../../');
    $this->newProjectDir = dirname($this->projectDirectory) . '/blt-project';
    $this->config = Yaml::parse(file_get_contents("{$this->newProjectDir}/project.yml"));
  }

  /**
   * Tests Phing pt:create target.
   */
  public function testBltCreate() {

    $this->assertFileExists($this->newProjectDir);
    $this->assertFileNotExists($this->newProjectDir . '/install');
    $this->assertFileNotExists($this->newProjectDir . '/tests/phpunit/BltTest.php');
    $this->assertFileExists($this->newProjectDir . '/vendor');
    $this->assertNotContains(
          'pt:self-test',
          file_get_contents($this->newProjectDir . '/.travis.yml')
      );
    $this->assertFileNotExists($this->newProjectDir . '/build/tasks/blt.xml');
    $this->assertNotContains(
          '${project.machine_name}',
          file_get_contents($this->newProjectDir . '/docroot/sites/default/settings.php')
      );
  }

}
