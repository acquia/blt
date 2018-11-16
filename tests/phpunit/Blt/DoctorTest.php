<?php

namespace Acquia\Blt\Tests\Blt;

use Acquia\Blt\Tests\BltProjectTestBase;

/**
 * Class DoctorTest.
 *
 * @group requires-db
 */
class DoctorTest extends BltProjectTestBase {

  public function testDoctorCommand() {
    $this->installDrupalMinimal();
    $this->blt("doctor");
  }

}
