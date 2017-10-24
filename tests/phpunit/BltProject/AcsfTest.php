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
   * @group blted8
   */
  public function testAcsfInit() {
    // @todo Add test back after https://github.com/acquia/blt/issues/2094.
    // @codingStandardsIgnoreStart
    # $this->assertFileExists($this->projectDirectory . '/docroot/modules/contrib/acsf');
    # $this->assertFileExists($this->projectDirectory . '/factory-hooks');
    // @codingStandardsIgnoreEnd
  }

}
