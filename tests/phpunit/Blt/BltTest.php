<?php

namespace Acquia\Blt\Tests\Blt;

use Acquia\Blt\Tests\BltTestBase;

/**
 * Class BLTTest.
 *
 * Verifies that project structure and configuration matches BLT
 * standards.
 */
class BltTest extends BltTestBase {

  /**
   * Tests BLT's project creation.
   *
   * @group blt
   */
  public function testBltCreate() {

    $this->assertFileExists($this->newProjectDir);
    $this->assertFileNotExists($this->newProjectDir . '/install');
    $this->assertFileNotExists($this->newProjectDir . '/tests/phpunit/BltTest.php');
    $this->assertFileExists($this->newProjectDir . '/vendor');
    $this->assertFileNotExists($this->newProjectDir . '/build/tasks/blt.xml');
    $this->assertNotContains(
          '${project.machine_name}',
          file_get_contents($this->newProjectDir . '/docroot/sites/default/settings.php')
      );
  }

}
