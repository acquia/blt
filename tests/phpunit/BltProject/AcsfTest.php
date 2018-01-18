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
   */
  public function testAcsfInit() {
    $this->blt("acsf:init");
    $this->assertFileExists($this->sandboxInstance . '/docroot/modules/contrib/acsf');
    $this->assertFileExists($this->sandboxInstance . '/factory-hooks');
  }

  public function tearDown() {
    // We modified existing sandbox files, so it needs to be recreated.
    $this->sandboxManager->removeSandboxInstance();
    parent::tearDown();
  }

}
