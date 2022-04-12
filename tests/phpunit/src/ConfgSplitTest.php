<?php

namespace Acquia\Blt\Tests;

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
