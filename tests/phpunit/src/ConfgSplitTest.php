<?php

namespace Acquia\Blt\Tests;

use Acquia\Blt\Robo\Common\YamlMunge;

/**
 * Tests Config Split recipe.
 */
class ConfgSplitTest extends BltProjectTestBase {

  /**
   * Tests recipes:config:init:splits to validate split creation.
   */
  public function testConfigSplitRecipe() {
    $this->blt('recipes:config:init:splits');
    $splits = $this->getSplits();
    foreach ($splits as $split) {
      $this->assertFileExists("$this->sandboxInstance/config/default/config_split.config_split.$split.yml");
      $this->assertDirectoryExists("$this->sandboxInstance/config/envs/$split");
    }
  }

  /**
   * Tests recipes:config:init:splits to validate core.extension manipulation.
   */
  public function testCoreExtension() {
    $core_extensions = YamlMunge::parseFile($this->sandboxInstance . '/config/default/core.extension.yml');
    $this->assertArrayHasKey("config_filter", $core_extensions['module']);
    $this->assertArrayHasKey("config_split", $core_extensions['module']);
  }

  /**
   * Defines the default BLT config splits.
   *
   * @return array
   *   An array of default config splits.
   */
  public function getSplits() {
    $splits = [
      'local',
      'dev',
      'stage',
      'prod',
      'ci',
    ];
    return $splits;
  }
}
