<?php

namespace Acquia\Blt\Tests\Blt;

use Acquia\Blt\Tests\BltProjectTestBase;

/**
 * Class LightningTest.
 */
class LightningTest extends BltProjectTestBase {
  public function testSetup() {
    $this->blt("setup", [
      '--define' => [
        'project.profile.name=lightning',
      ],
      '--yes' => TRUE,
    ]);
  }

}
