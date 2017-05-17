<?php

namespace Acquia\Blt\Tests\BltProject;

use Acquia\Blt\Tests\BltProjectTestBase;

/**
 * Class BehatTest.
 *
 * Verifies that behat configuration is as expected.
 */
class BehatTest extends BltProjectTestBase {

  /**
   * Tests setup:behat command.
   *
   * @group blt-project
   */
  public function testSetupBehat() {

    // Assert that a local.yml file was created in the new project.
    $this->assertFileExists($this->projectDirectory . '/tests/behat/local.yml');
    $this->assertNotContains(
          '${local_url}',
          file_get_contents("{$this->projectDirectory}/tests/behat/local.yml")
      );
  }

}
