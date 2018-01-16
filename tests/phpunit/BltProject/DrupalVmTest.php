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
   * @group vm
   */
  public function testVmInit() {
    $this->assertFileExists($this->sandboxInstance . '/Vagrantfile');
    $this->assertFileExists($this->sandboxInstance . '/box/config.yml');
    $this->assertFileExists($this->sandboxInstance . '/blt/project.local.yml');

    $this->assertNotContains(
      '${project.machine_name}',
      file_get_contents($this->sandboxInstance . '/box/config.yml')
    );
    $this->assertContains(
      'local',
      file_get_contents($this->sandboxInstance . '/drush/sites/' . $this->config->get('project.machine_name') . '.site.yml')
    );
    $this->assertContains(
      'http://127.0.0.1:8888',
      file_get_contents($this->sandboxInstance . '/drush/sites/' . $this->config->get('project.machine_name') . '.site.yml')
    );
    $this->assertContains(
      $this->config->get('project.machine_name') . '.local',
      file_get_contents($this->sandboxInstance . '/blt/project.local.yml')
    );
  }

}
