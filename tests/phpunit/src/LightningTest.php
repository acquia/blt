<?php

namespace Acquia\Blt\Tests\BltProject;

use Acquia\Blt\Tests\BltProjectTestBase;

/**
 * Class LightningTest.
 *
 * @group requires-db
 * @group orca_ignore
 */
class LightningTest extends BltProjectTestBase {

  public function testSetup() {
    list($status_code) = $this->blt("setup", [
      '--define' => [
        'project.profile.name=lightning',
      ],
    ]);
    $this->assertEquals(0, $status_code);
  }

}
