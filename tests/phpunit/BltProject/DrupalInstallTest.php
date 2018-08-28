<?php

namespace Acquia\Blt\Tests\Blt;

use Acquia\Blt\Tests\BltProjectTestBase;

/**
 * Class SetupCommandTest.
 *
 * @group requires-db
 */
class DrupalInstallTest extends BltProjectTestBase {

  public function testInstallStrategy() {
    $this->fs->remove($this->sandboxInstance . "/docroot/modules/constrib/search_api");
    list($status_code, $output, $config) = $this->blt("setup", [
      '--define' => [
        'setup.strategy=install',
        'project.profile.name=minimal',
      ],
    ], FALSE);
    $this->assertEquals(1, $status_code);
  }

}