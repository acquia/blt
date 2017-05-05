<?php

namespace Drupal\Tests\PHPUnit;

/**
 * Class BehatTest.
 *
 * Verifies that behat configuration is as expected.
 */
class BehatTest extends TestBase {

  /**
   * Tests Phing setup:behat target.
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
