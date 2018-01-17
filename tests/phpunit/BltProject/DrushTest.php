<?php

namespace Acquia\Blt\Tests\BltProject;

use Acquia\Blt\Tests\BltProjectTestBase;

/**
 * Class DrushTest.
 */
class DrushTest extends BltProjectTestBase {

  /**
   * Tests that correct drush configuration is loaded.
   *
   * @group blted8
   */
  public function testDrushConfig() {

    // We must define the absolute path of the binary because child shell
    // processes in PHP to not inherit $PATH setting from environment.
    $drush_bin = $this->sandboxInstance . '/vendor/bin/drush';
    // Use --format=json output so we don't have to deal with output line
    // wrapping when running the tests.
    $command = "$drush_bin status --format=json";

    // Test that drush can be run from the following directories.
    $dirs = array(
      $this->sandboxInstance,
      $this->sandboxInstance . '/docroot',
      $this->sandboxInstance . '/docroot/sites/default',
    );
    $config_file = $this->sandboxInstance . '/vendor/drush/drush/drush.yml';

    foreach ($dirs as $dir) {
      chdir($dir);
      $json_output = shell_exec($command);
      $drush_output = json_decode($json_output, TRUE);

      $message = "Failed asserting that the output of `$command` contains $config_file when executed from $dir.";
      $this->assertContains($config_file, $drush_output['drush-conf'][0], $message);

      $config_file = $this->sandboxInstance . '/drush/drush.yml';
      $message = "Failed asserting that the output of `$command` contains $config_file when executed from $dir.";
      $this->assertContains($config_file, $drush_output['drush-conf'][1], $message);

    }
  }

}
