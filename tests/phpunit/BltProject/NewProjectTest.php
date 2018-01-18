<?php

namespace Acquia\Blt\Tests\Blt;

use Acquia\Blt\Tests\BltProjectTestBase;

/**
 * Class BLTTest.
 */
class NewProjectTest extends BltProjectTestBase {

  /**
   * Tests BLT's project creation.
   */
  public function testBltCreate() {
    $this->assertFileExists($this->sandboxInstance);
    $this->assertFileExists($this->sandboxInstance . '/vendor');
    $this->assertFileExists($this->sandboxInstance . '/docroot/sites/default/settings/local.settings.php');
  }

}
