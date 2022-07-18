<?php

namespace Acquia\Blt\Tests;

/**
 * Test blt setup.
 */
class RunServerTest extends BltProjectTestBase {

  public function testRunServer() {
    [$status_code] = $this->blt("tests:server");
    $this->assertEquals(0, $status_code);
  }

}
