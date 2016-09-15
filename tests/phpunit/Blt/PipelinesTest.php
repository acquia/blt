<?php

namespace Acquia\Blt\Tests\Blt;

use Acquia\Blt\Tests\BltTestBase;

/**
 * Class PipelinesTest.
 *
 * Verifies that Pipelines support has been initialized.
 */
class PipelinesTest extends BltTestBase {

  /**
   * Tests Phing ci:travis:init target.
   */
  public function testTravisInit() {
    $this->assertFileExists($this->new_project_dir . '/acquia-pipelines.yml');
  }

}
