<?php

namespace Drupal\Tests\PHPUnit;

/**
 * Class DrushTest.
 *
 * Verifies that git related tasks work as expected.
 */
class DrushTest extends TestBase {

  /**
   * Tests that correct drush configuration is loaded.
   */
  public function testDrushConfig() {

    // We must define the absolute path of the binary because child shell
    // processes in PHP to not inherit $PATH setting from environment.
    $drush_bin = $this->projectDirectory . '/vendor/bin/drush';
    $command = "$drush_bin status";

    // Test that drush can be run from the following directories.
    $dirs = array(
      $this->projectDirectory . '/docroot',
      $this->projectDirectory . '/docroot/sites/default',
    );

    foreach ($dirs as $dir) {
      chdir($dir);
      print "Executing \"$command\" in $dir \n";
      // If it contains the local URI, we know it is correctly loading
      // drushrc.php.
      $this->assertContains('http://127.0.0.1:8888', shell_exec($command));
    }
  }

}
