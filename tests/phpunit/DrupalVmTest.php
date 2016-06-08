<?php

namespace Drupal;

use Symfony\Component\Yaml\Yaml;

/**
 * Class DrupalVM.
 *
 * Verifies that Drupal VM integration works as expected.
 */
class DrupalVmTest extends \PHPUnit_Framework_TestCase {

  /**
   * Class constructor.
   */
  public function __construct() {
    $this->projectDirectory = realpath(dirname(__FILE__) . '/../../');
    $this->config = Yaml::parse(file_get_contents("{$this->projectDirectory}/project.yml"));
    $this->new_project_dir = dirname($this->projectDirectory) . '/' . $this->config['project']['machine_name'];
  }

  /**
   * Tests Phing vm:init target.
   */
  public function testVmInit() {
    $this->assertFileExists($this->new_project_dir . '/Vagrantfile');
    $this->assertFileExists($this->new_project_dir . '/box/config.yml');
    $this->assertFileExists($this->new_project_dir . '/drush/site-aliases/drupal-vm.aliases.drushrc.php');

    $this->assertNotContains(
      '${project.machine_name}',
      file_get_contents($this->new_project_dir . '/box/config.yml')
    );
    $this->assertNotContains(
      '${project.machine_name}',
      file_get_contents($this->new_project_dir . '/drush/site-aliases/drupal-vm.aliases.drushrc.php')
    );
    $this->assertContains(
      'drush:',
      file_get_contents($this->new_project_dir . '/build/custom/phing/build.yml')
    );
    $this->assertContains(
      'root:',
      file_get_contents($this->new_project_dir . '/build/custom/phing/build.yml')
    );
  }

}
