<?php

namespace Acquia\Blt\Tests;

use Acquia\Blt\Robo\Common\YamlMunge;
use Symfony\Component\Yaml\Yaml;

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
    $this->blt('vm', [
      '--no-boot' => TRUE,
    ]);
    $this->assertFileExists($this->sandboxInstance . '/Vagrantfile');
    $this->assertFileExists($this->sandboxInstance . '/box/config.yml');
    $this->assertFileExists($this->sandboxInstance . '/blt/local.blt.yml');

    $drush_alias_file = $this->sandboxInstance . '/drush/sites/' . $this->config->get('project.machine_name') . '.site.yml';
    $drush_alias_contents = YamlMunge::parseFile($drush_alias_file);

    $this->assertStringNotContainsString(
      '${project.machine_name}',
      file_get_contents($this->sandboxInstance . '/box/config.yml')
    );
    $this->assertArrayHasKey('local', $drush_alias_contents, print_r($drush_alias_contents, TRUE));
    $this->assertEquals($this->config->get('project.local.uri'), $drush_alias_contents['local']['uri']);

    $local_config = Yaml::parseFile($this->sandboxInstance . '/blt/local.blt.yml');
    $this->assertArrayHasKey('vm', $local_config);
    $this->assertArrayHasKey('enable', $local_config['vm']);
    $this->assertEquals(TRUE, $local_config['vm']['enable']);
  }

}
