<?php

namespace Acquia\Blt\Tests\BltProject;

use Acquia\Blt\Tests\BltProjectTestBase;

/**
 * Class BehatTest.
 *
 * @group orca_ignore
 */
class TestBehatCommandTest extends BltProjectTestBase {

  /**
   * Tests tests:behat:init:config command.
   */
  public function testSetupBehat() {
    $this->blt("recipes:behat:init");
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
    $this->blt("recipes:behat:init");
    $this->installDrupalMinimal();
    list($status_code) = $this->blt("tests:behat:run");
    $this->assertEquals(0, $status_code);
    list($status_code) = $this->blt("tests:behat:list:definitions");
    $this->assertEquals(0, $status_code);
  }

}
