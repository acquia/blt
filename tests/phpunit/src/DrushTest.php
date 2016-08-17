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
   *
   * @group drush
   */
  public function testDrushConfig() {

    // We must define the absolute path of the binary because child shell
    // processes in PHP to not inherit $PATH setting from environment.
    $drush_bin = $this->projectDirectory . '/vendor/bin/drush';
    $command = "$drush_bin status";

    // Test that drush can be run from the following directories.
    $dirs = array(
      $this->projectDirectory,
      $this->projectDirectory . '/docroot',
      $this->projectDirectory . '/docroot/sites/default',
    );

    foreach ($dirs as $dir) {
      chdir($dir);
      print "Executing \"$command\" in $dir \n";
      // Check for the path to drushrc.php that is included in the project.
      $this->assertContains($this->projectDirectory . '/drush/drushrc.php', shell_exec($command));
    }
  }

}
