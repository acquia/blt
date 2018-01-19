<?php

namespace Acquia\Blt\Tests\Blt;

use Acquia\Blt\Tests\BltProjectTestBase;

/**
 * Class BltConfigTest.
 */
class BltConfigTest extends BltProjectTestBase {

  /**
   * Tests ci:pipelines:init command.
   *
   * @group blted8
   */
  public function testConfig() {
    $this->blt('config:get', []);
  }

}
