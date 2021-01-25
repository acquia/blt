<?php

namespace Acquia\Blt\Tests;

/**
 * Tests the validate command.
 */
class ValidateCommandTest extends BltProjectTestBase {

  public function testValidateCommand() {
    $this->blt("validate");
  }

}
