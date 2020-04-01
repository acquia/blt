<?php

namespace Acquia\Blt\Tests;

/**
 * Class ValidateCommandTest.
 */
class ValidateCommandTest extends BltProjectTestBase {

  public function testValidateCommand() {
    $this->blt("validate");
  }

}
