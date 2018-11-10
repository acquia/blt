<?php

namespace Acquia\Blt\Tests\Blt;

use Acquia\Blt\Tests\BltProjectTestBase;

/**
 * Class ValidateCommandTest.
 */
class ValidateCommandTest extends BltProjectTestBase {

  public function testValidateCommand() {
    $this->blt("validate");
  }

}
