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
    list($status_code, $output, $config) = $this->blt("doctor");
    $this->assertEquals(0, $status_code);
  }

}
