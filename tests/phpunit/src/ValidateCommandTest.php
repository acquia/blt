<?php

namespace Acquia\Blt\Tests;

/**
 * Class ValidateCommandTest.
 *
 * @group orca_ignore
 */
class ValidateCommandTest extends BltProjectTestBase {

  public function testValidateCommand() {
    $this->blt("validate");
  }

}
