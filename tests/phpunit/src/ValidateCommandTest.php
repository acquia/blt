<?php

namespace Acquia\Blt\Tests\BltProject;

use Acquia\Blt\Tests\BltProjectTestBase;

/**
 * Class ValidateCommandTest.
 */
class ValidateCommandTest extends BltProjectTestBase {

  public function testValidateCommand() {
    $this->blt("validate");
  }

}
