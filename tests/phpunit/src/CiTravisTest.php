<?php

namespace Acquia\Blt\Tests;

/**
 * Class TravisCiTest.
 */
class CiTravisTest extends BltProjectTestBase {

  /**
   * Tests recipes:ci:travis:init command.
   */
  public function testTravisInit() {
    $this->blt('recipes:ci:travis:init');
    $this->assertFileExists($this->sandboxInstance . '/.travis.yml');
  }

}
