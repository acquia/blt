<?php

namespace Acquia\Blt\Tests\BltProject;

use Acquia\Blt\Tests\BltProjectTestBase;

/**
 * Class TravisCiTest.
 *
 * @group orca_ignore
 */
class CiTravisTest extends BltProjectTestBase {

  /**
   * Tests recipes:ci:travis:init command.
   */
  public function testTravisInit() {
    $this->blt('recipes:ci:travis:init');
    $this->assertFileExists($this->sandboxInstance . '/.travis.yml');
  }

}
