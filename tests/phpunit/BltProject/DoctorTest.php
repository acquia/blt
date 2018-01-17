<?php

namespace Acquia\Blt\Tests\Blt;

use Acquia\Blt\Tests\BltProjectTestBase;

/**
 * Class DoctorTest.
 */
class DoctorTest extends BltProjectTestBase {

  public function testDoctorCommand() {
    $this->importDbFromFixture();
    $this->blt("doctor");
  }

}
