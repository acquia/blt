<?php

namespace Drupal;

use Symfony\Component\Yaml\Yaml;

/**
 * Class AcsfTest.
 *
 * Verifies that project structure and configuration matches BLT
 * standards.
 */
class AcsfTest extends \PHPUnit_Framework_TestCase {

  /**
   * Class constructor.
   */
  public function __construct() {
    $this->projectDirectory = realpath(dirname(__FILE__) . '/../../');
    $this->config = Yaml::parse(file_get_contents("{$this->projectDirectory}/project.yml"));
    $this->new_project_dir = dirname($this->projectDirectory) . '/' . $this->config['project']['machine_name'];
  }

  /**
   * Tests Phing acsf:init target.
   */
  public function testAcsfInit() {
    $this->assertFileExists($this->new_project_dir . '/docroot/modules/contrib/acsf');
    $this->assertFileExists($this->new_project_dir . '/factory-hooks');
  }

}
