<?php

namespace Acquia\Blt\Tests\Blt;

use Acquia\Blt\Tests\BltProjectTestBase;

/**
 * Class TravisCiTest.
 *
 * Verifies that Travis CI support has been initialized.
 */
class TravisCiTest extends BltProjectTestBase {

  /**
   * Tests ci:travis:init command.
   *
   * @group blt-project
   */
  public function testTravisInit() {
    $this->assertFileExists($this->projectDirectory . '/.travis.yml');
  }

}
