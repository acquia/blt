<?php

namespace Acquia\Blt\Tests\BltProject;

use Acquia\Blt\Tests\BltProjectTestBase;

/**
 * Class DoctorTest.
 *
 * @group requires-db
 * @group orca_ignore
 */
class DoctorTest extends BltProjectTestBase {

  public function testDoctorCommand() {
    $this->installDrupalMinimal();
    list($status_code) = $this->blt("doctor");
    $this->assertEquals(0, $status_code);
  }

}
