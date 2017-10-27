<?php

namespace Acquia\Blt\Tests\Blt;

use Acquia\Blt\Tests\BltProjectTestBase;

/**
 * Class BLTTest.
 *
 * Verifies that project structure and configuration matches BLT
 * standards.
 */
class NewProjectTest extends BltProjectTestBase {

  /**
   * Tests BLT's project creation.
   *
   * @group blted8
   */
  public function testBltCreate() {
    $this->assertFileExists($this->projectDirectory);
    $this->assertFileNotExists($this->projectDirectory . '/install');
    $this->assertFileNotExists($this->projectDirectory . '/tests/phpunit/BltTest.php');
    $this->assertFileExists($this->projectDirectory . '/vendor');
    $this->assertNotContains(
          '${project.machine_name}',
          file_get_contents($this->projectDirectory . '/docroot/sites/default/settings.php')
      );
  }

}
