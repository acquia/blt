<?php

namespace Acquia\Blt\Tests\BltProject;

use Acquia\Blt\Tests\BltProjectTestBase;

/**
 * Class DrushTest.
 *
 * Verifies that git related tasks work as expected.
 */
class DrushTest extends BltProjectTestBase {

  /**
   * Tests that correct drush configuration is loaded.
   *
   * @group blt-project
   */
  public function testDrushConfig() {

    // We must define the absolute path of the binary because child shell
    // processes in PHP to not inherit $PATH setting from environment.
    $drush_bin = $this->projectDirectory . '/vendor/bin/drush';
    // Use --format=json output so we don't have to deal with output line
    // wrapping when running the tests.
    $command = "$drush_bin status --format=json";

    // Test that drush can be run from the following directories.
    $dirs = array(
      $this->projectDirectory . '/docroot',
      $this->projectDirectory . '/docroot/sites/default',
    );

    foreach ($dirs as $dir) {
      chdir($dir);
      print "Executing \"$command\" in $dir \n";
      $json_output = shell_exec($command);
      $drush_output = json_decode($json_output, TRUE);
      // Check for the path to drushrc.php that is included in the project.
      $this->assertContains($this->projectDirectory . '/drush/drushrc.php', $drush_output['drush-conf']);
    }
  }

}
