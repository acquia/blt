<?php

namespace Acquia\Blt\Tests;

/**
 * Test Doctor.
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
