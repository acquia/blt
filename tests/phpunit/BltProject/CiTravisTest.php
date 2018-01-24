<?php

namespace Acquia\Blt\Tests\Blt;

use Acquia\Blt\Tests\BltProjectTestBase;

/**
 * Class TravisCiTest.
 */
class TravisCiTest extends BltProjectTestBase {

  /**
   * Tests recipes:ci:travis:init command.
   */
  public function testTravisInit() {
    $this->blt('recipes:ci:travis:init');
    $this->assertFileExists($this->sandboxInstance . '/.travis.yml');
  }

}
