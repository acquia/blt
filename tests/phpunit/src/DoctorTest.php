<?php

namespace Acquia\Blt\Tests;

/**
 * Class DoctorTest.
 */
class DoctorTest extends BltProjectTestBase {

  public function testDoctorCommand() {
    $this->blt('blt:init:settings');
    $this->blt('setup:behat');
    list($status_code) = $this->blt("doctor");
    $this->assertEquals(0, $status_code);
  }

}
