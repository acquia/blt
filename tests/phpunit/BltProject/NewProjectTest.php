<?php

namespace Acquia\Blt\Tests\Blt;

use Acquia\Blt\Tests\BltProjectTestBase;

/**
 * Class BLTTest.
 */
class NewProjectTest extends BltProjectTestBase {

  /**
   * Tests BLT's project creation.
   *
   * @group blted8
   */
  public function testBltCreate() {
    $this->assertFileExists($this->sandboxInstance);
    $this->assertFileNotExists($this->sandboxInstance . '/install');
    $this->assertFileNotExists($this->sandboxInstance . '/tests/phpunit/BltTest.php');
    $this->assertFileExists($this->sandboxInstance . '/vendor');
    $this->assertNotContains(
          '${project.machine_name}',
          file_get_contents($this->sandboxInstance . '/docroot/sites/default/settings.php')
      );
  }

}
