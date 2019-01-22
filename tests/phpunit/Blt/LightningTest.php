<?php

namespace Acquia\Blt\Tests\Blt;

use Acquia\Blt\Tests\BltProjectTestBase;

/**
 * Class LightningTest.
 *
 * @group requires-db
 */
class LightningTest extends BltProjectTestBase {

  public function testSetup() {
    list($status_code, $output, $config) = $this->blt("setup", [
      '--define' => [
        'project.profile.name=lightning',
      ],
    ]);
    $this->assertEquals(0, $status_code);
  }

}
