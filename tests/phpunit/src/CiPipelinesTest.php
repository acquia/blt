<?php

namespace Acquia\Blt\Tests;

/**
 * Tests Pipelines recipe.
 */
class CiPipelinesTest extends BltProjectTestBase {

  /**
   * Tests recipes:ci:pipelines:init command.
   *
   * @group blted8
   */
  public function testPipelinesInit() {
    $this->blt('recipes:ci:pipelines:init');
    $this->assertFileExists($this->sandboxInstance . '/acquia-pipelines.yaml');
  }

}
