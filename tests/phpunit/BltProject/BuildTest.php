<?php

namespace Acquia\Blt\Tests\BltProject;

use Acquia\Blt\Tests\BltProjectTestBase;

/**
 * Class BuildTest.
 *
 * Verifies that build tasks work as expected.
 */
class BuildTest extends BltProjectTestBase {

  /**
   * Tests Phing setup:make target.
   *
   * This should build /make.yml.
   *
   * @group blt-project
   */
  public function testSetupMake() {

    $this->assertFileExists($this->projectDirectory . '/docroot/index.php');
    $this->assertFileExists($this->projectDirectory . '/docroot/modules/contrib');
    $this->assertFileExists($this->projectDirectory . '/docroot/themes/custom');
  }

}
