<?php

namespace Acquia\Blt\Tests\Blt;

use Acquia\Blt\Tests\BltProjectTestBase;

/**
 * Class LightningTest.
 */
class LightningTest extends BltProjectTestBase {
  public function testSetup() {
    $args = [
      '--define' => [
        'project.profile.name=lightning',
      ],
    ];
    $args[] = '--yes';
    $this->blt("setup", $args);
  }

}
