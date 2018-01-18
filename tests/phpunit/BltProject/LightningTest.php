<?php

namespace Acquia\Blt\Tests\Blt;

use Acquia\Blt\Tests\BltProjectTestBase;

/**
 * Class LightningTest.
 */
class LightningTest extends BltProjectTestBase {

  public function testSetup() {
    // @todo Determine why this is necessary on Travis but not locally.
    $this->fs->remove($this->sandboxInstance . "/config/default");
    $this->fs->mkdir($this->sandboxInstance . "/config/default");

    $this->blt("setup", [
      '--define' => [
        'project.profile.name=lightning',
      ],
    ]);
  }

}
