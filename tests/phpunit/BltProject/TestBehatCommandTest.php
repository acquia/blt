<?php

namespace Acquia\Blt\Tests\BltProject;

use Acquia\Blt\Tests\BltProjectTestBase;

/**
 * Class BehatTest.
 */
class BehatTest extends BltProjectTestBase {

  /**
   * Tests setup:behat command.
   */
  public function testSetupBehat() {
    $this->blt("setup:behat");
    // Assert that a local.yml file was created in the new project.
    $this->assertFileExists($this->sandboxInstance . '/tests/behat/local.yml');
    $this->assertNotContains(
          '${local_url}',
          file_get_contents("{$this->sandboxInstance}/tests/behat/local.yml")
      );
  }

  /**
   *
   */
  public function testBehatCommand() {
    $this->installDrupalMinimal();
    $this->blt("tests:behat");
    $this->blt("tests:behat:definitions");
  }

}
