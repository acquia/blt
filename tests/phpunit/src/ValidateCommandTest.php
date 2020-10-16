<?php

namespace Acquia\Blt\Tests;

/**
 * Test Validate command.
 *
 * @group orca_ignore
 */
class ValidateCommandTest extends BltProjectTestBase {

  public function testValidateCommand() {
    $this->blt("validate");
  }

}
