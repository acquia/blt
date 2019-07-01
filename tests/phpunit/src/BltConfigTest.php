<?php

namespace Acquia\Blt\Tests\BltProject;

use Acquia\Blt\Tests\BltProjectTestBase;

/**
 * Class BltConfigTest.
 *
 * @group orca_ignore
 */
class BltConfigTest extends BltProjectTestBase {

  /**
   * Tests blt:config:dump command.
   */
  public function testPipelinesInit() {
    $this->blt('blt:config:dump', []);
    list(, $output,) = $this->blt('blt:config:dump', [
      '--site' => 'site2',
    ]);
    $this->assertContains('site2', $output);
    $this->blt('blt:config:dump', [
      '--environment' => 'local',
    ]);
    $this->blt('blt:config:dump', [
      '--environment' => 'ci',
    ]);
  }

}
