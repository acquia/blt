<?php

namespace Acquia\Blt\Tests\Blt;

use Acquia\Blt\Tests\BltTestBase;

/**
 * Class AcsfTest.
 *
 * Verifies that acsf support has been initialized.
 */
class AcsfTest extends BltTestBase {

  /**
   * Tests acsf:init command.
   */
  public function testAcsfInit() {
    $this->assertFileExists($this->new_project_dir . '/docroot/modules/contrib/acsf');
    $this->assertFileExists($this->new_project_dir . '/factory-hooks');
  }

}
