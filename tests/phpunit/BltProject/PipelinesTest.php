<?php

namespace Acquia\Blt\Tests\Blt;

use Acquia\Blt\Tests\BltProjectTestBase;

/**
 * Class PipelinesTest.
 *
 * Verifies that Pipelines support has been initialized.
 */
class PipelinesTest extends BltProjectTestBase {

  /**
   * Tests ci:pipelines:init command.
   *
   * @group blt-project
   */
  public function testPipelinesInit() {
    $this->assertFileExists($this->projectDirectory . '/acquia-pipelines.yml');
  }

}
