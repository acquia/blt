<?php

namespace Acquia\Blt\Tests\Blt;

use Acquia\Blt\Tests\BltTestBase;

/**
 * Class DrupalVM.
 *
 * Verifies that Drupal VM integration works as expected.
 */
class DrupalVmTest extends BltTestBase {

  /**
   * Tests vm:init command.
   *
   * @group blt
   */
  public function testVmInit() {
    $this->assertFileExists($this->newProjectDir . '/Vagrantfile');
    $this->assertFileExists($this->newProjectDir . '/box/config.yml');
    $this->assertFileExists($this->newProjectDir . '/blt/project.local.yml');
    $this->assertFileExists($this->newProjectDir . '/drush/site-aliases/aliases.drushrc.php');

    $this->assertNotContains(
      '${project.machine_name}',
      file_get_contents($this->newProjectDir . '/box/config.yml')
    );
    $this->assertContains(
      $this->config['project']['machine_name'] . '.local',
      file_get_contents($this->newProjectDir . '/drush/site-aliases/aliases.drushrc.php')
    );
    $this->assertContains(
      $this->config['project']['machine_name'] . '.local',
      file_get_contents($this->newProjectDir . '/blt/project.local.yml')
    );
  }

}
