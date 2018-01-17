<?php

namespace Acquia\Blt\Tests\Blt;

use Acquia\Blt\Tests\BltProjectTestBase;

/**
 * Class TravisCiTest.
 */
class TravisCiTest extends BltProjectTestBase {

  /**
   * Tests ci:travis:init command.
   *
   * @group blted8
   */
  public function testTravisInit() {
    $this->blt('ci:travis:init');
    $this->assertFileExists($this->sandboxInstance . '/.travis.yml');
  }

}
