<?php

namespace Acquia\Blt\Tests\Blt;

use Acquia\Blt\Tests\BltProjectTestBase;

/**
 * Class AcsfTest.
 *
 * Verifies that acsf support has been initialized.
 */
class AcsfTest extends BltProjectTestBase {

  /**
   * Tests acsf:init command.
   *
   * @group blt-project
   */
  public function testAcsfInit() {
    $this->assertFileExists($this->projectDirectory . '/docroot/modules/contrib/acsf');
    $this->assertFileExists($this->projectDirectory . '/factory-hooks');
  }

}
