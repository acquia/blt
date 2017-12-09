<?php

namespace Acquia\Blt\Tests\Blt;

use Acquia\Blt\Tests\BltProjectTestBase;

/**
 * Class DrupalVM.
 *
 * Verifies that Drupal VM integration works as expected.
 */
class DrupalVmTest extends BltProjectTestBase {

  /**
   * Tests vm:init command.
   *
   * @group blted8
   */
  public function testVmInit() {
    $this->assertFileExists($this->projectDirectory . '/Vagrantfile');
    $this->assertFileExists($this->projectDirectory . '/box/config.yml');
    $this->assertFileExists($this->projectDirectory . '/blt/project.local.yml');

    $this->assertNotContains(
      '${project.machine_name}',
      file_get_contents($this->projectDirectory . '/box/config.yml')
    );
    $this->assertContains(
      'local',
      file_get_contents($this->projectDirectory . '/drush/sites/' . $this->config['project']['machine_name'] . '.site.yml')
    );
    $this->assertContains(
      'http://127.0.0.1:8888',
      file_get_contents($this->projectDirectory . '/drush/sites/' . $this->config['project']['machine_name'] . '.site.yml')
    );
    $this->assertContains(
      $this->config['project']['machine_name'] . '.local',
      file_get_contents($this->projectDirectory . '/blt/project.local.yml')
    );
  }

}
