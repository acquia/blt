<?php

namespace Acquia\Blt\Tests\Blt;

use Acquia\Blt\Tests\BltTestBase;

/**
 * Class TravisCiTest.
 *
 * Verifies that Travis CI support has been initialized.
 */
class TravisCiTest extends BltTestBase {

  /**
   * Tests ci:travis:init command.
   */
  public function testTravisInit() {
    $this->assertFileExists($this->new_project_dir . '/.travis.yml');
  }

}
