<?php

namespace Acquia\Blt\Tests\BltProject;

use Acquia\Blt\Tests\BltProjectTestBase;

/**
 * Class PipelinesTest.
 *
 * @group orca_ignore
 */
class CiPipelinesTest extends BltProjectTestBase {

  /**
   * Tests recipes:ci:pipelines:init command.
   *
   * @group blted8
   */
  public function testPipelinesInit() {
    $this->blt('recipes:ci:pipelines:init');
    $this->assertFileExists($this->sandboxInstance . '/acquia-pipelines.yml');
  }

}
