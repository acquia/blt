<?php

namespace Acquia\Blt\Tests\Blt;

use Acquia\Blt\Tests\BltProjectTestBase;

/**
 * Class CustomCommandTest.
 */
class CustomCommandTest extends BltProjectTestBase {

  public function testExampleCustomCommand() {
    $this->blt("examples:init");
    $this->blt("custom:hello");
  }

}
