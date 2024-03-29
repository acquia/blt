<?php

namespace Acquia\Blt\Tests;

/**
 * Tests Drush integration.
 */
class DrushTest extends BltProjectTestBase {

  /**
   * Tests that correct drush configuration is loaded.
   *
   * @group blted8
   * @group orca_ignore
   */
  public function testDrushConfig() {
    // Test that drush can be run from the following directories.
    $dirs = [
      $this->sandboxInstance,
      $this->sandboxInstance . '/docroot',
      $this->sandboxInstance . '/docroot/sites/default',
    ];

    foreach ($dirs as $dir) {
      chdir($dir);
      $drush_output = $this->drushJson(['status']);

      $config_file = $this->sandboxInstance . '/vendor/drush/drush/drush.yml';
      $message = "Failed asserting that the output of `drush status` contains $config_file when executed from $dir.";
      $this->assertContains($config_file, $drush_output['drush-conf'], $message);

      $config_file = $this->sandboxInstance . '/drush/drush.yml';
      $message = "Failed asserting that the output of `drush status` contains $config_file when executed from $dir.";
      $this->assertContains($config_file, $drush_output['drush-conf'], $message);

    }
  }

}
