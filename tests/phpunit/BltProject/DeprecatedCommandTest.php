<?php

namespace Acquia\Blt\Tests\Blt;

use Acquia\Blt\Tests\BltProjectTestBase;

/**
 * Class DeprecatedCommandTest.
 */
class DeprecatedCommandTest extends BltProjectTestBase {

  /**
   * @group long
   */
  public function testValidateCommand() {
    $this->blt("tests:php:sniff:deprecated");
  }

}
