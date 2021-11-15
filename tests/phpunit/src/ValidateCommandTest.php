<?php

namespace Acquia\Blt\Tests;

/**
 * Tests the validate command.
 */
class ValidateCommandTest extends BltProjectTestBase {

  public function testValidateCommand() {
    list($status_code) = $this->blt("validate");

    static::assertEquals(0, $status_code);
  }

}
