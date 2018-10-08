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
    $this->blt("setup", [
      '--define' => [
        'project.profile.name=lightning',
      ],
    ]);
  }

}
