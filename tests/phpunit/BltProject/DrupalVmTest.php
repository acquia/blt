<?php

namespace Acquia\Blt\Tests\Blt;

use Acquia\Blt\Tests\BltProjectTestBase;
use Acquia\Blt\Robo\Common\YamlMunge;
use function print_r;

/**
 * Class DrupalVM.
 *
 * Verifies that Drupal VM integration works as expected.
 */
class DrupalVmTest extends BltProjectTestBase {

  /**
   * Tests vm:init command.
   */
  public function testVmInit() {
    $this->blt('vm --no-boot');
    $this->assertFileExists($this->sandboxInstance . '/Vagrantfile');
    $this->assertFileExists($this->sandboxInstance . '/box/config.yml');
    $this->assertFileExists($this->sandboxInstance . '/blt/project.local.yml');

    $drush_alias_file = $this->sandboxInstance . '/drush/sites/' . $this->config->get('project.machine_name') . '.site.yml';
    $drush_alias_contents = YamlMunge::parseFile($drush_alias_file);

    $this->assertNotContains(
      '${project.machine_name}',
      file_get_contents($this->sandboxInstance . '/box/config.yml')
    );
    $this->assertArrayHasKey('local', $drush_alias_contents, print_r($drush_alias_contents, TRUE));
    $this->assertEquals($this->config->get('project.local.uri'), $drush_alias_contents['local']['uri']);
    $this->assertContains(
      $this->config->get('project.machine_name') . '.local',
      file_get_contents($this->sandboxInstance . '/blt/project.local.yml')
    );
  }

  public function tearDown() {
    // We modified existing sandbox files, so it needs to be recreated.
    $this->bootstrapper->removeSandboxInstance();
    parent::tearDown();
  }

}
