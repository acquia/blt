<?php

namespace Acquia\Blt\Tests;

/**
 * Test blt setup.
 */
class RunServerTest extends BltProjectTestBase {

  public function testRunServer() {
    $this->blt("tests:server");
  }

}
