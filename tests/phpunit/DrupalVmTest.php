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
    $this->newProjectDir = dirname($this->projectDirectory) . '/blt-project';
    $this->config = Yaml::parse(file_get_contents("{$this->newProjectDir}/project.yml"));
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
      file_get_contents($this->new_project_dir . '/example.project.local.yml')
    );
    $this->assertContains(
      'root:',
      file_get_contents($this->new_project_dir . '/example.project.local.yml')
    );
    $this->assertContains(
      'drush:',
      file_get_contents($this->new_project_dir . '/project.local.yml')
    );
    $this->assertContains(
      'root:',
      file_get_contents($this->new_project_dir . '/project.local.yml')
    );
  }

}
