<?php

namespace Acquia\Blt\Tests\BltProject;

use Acquia\Blt\Tests\BltProjectTestBase;

/**
 * Class BehatTest.
 */
class BehatTest extends BltProjectTestBase {

  /**
   * Tests tests:behat:init:config command.
   */
  public function testSetupBehat() {
    $this->blt("tests:behat:init:config");
    // Assert that a local.yml file was created in the new project.
    $this->assertFileExists($this->sandboxInstance . '/tests/behat/local.yml');
    $this->assertNotContains(
          '${local_url}',
          file_get_contents("{$this->sandboxInstance}/tests/behat/local.yml")
      );
  }

  /**
   * @group requires-db
   */
  public function testBehatCommand() {
    $this->installDrupalMinimal();
    $this->blt("tests:behat:run");
    $this->blt("tests:behat:list:definitions");
  }

}
