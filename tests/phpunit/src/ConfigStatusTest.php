<?php

namespace Acquia\Blt\Tests;

/**
 * Test blt config imports.
 */
class ConfigStatusTest extends BltProjectTestBase {

  public function testSingleSiteConfig() {
    $this->installDrupalMinimal();
    $result = $this->inspector->isActiveConfigIdentical();
    $this->assertEquals(TRUE, $result);

  }

  public function testConfigDiff() {
    $this->installDrupalMinimal();
    // Export site config.
    $this->executor->drush("config-export")->interactive(FALSE)->run();

    // Validate Status (should pass).
    $result = $this->inspector->isActiveConfigIdentical();
    $this->assertEquals(TRUE, $result);

    // Change local copy of config.
    $this->fs->copy(
      $this->bltDirectory . "/tests/phpunit/fixtures/user.role.volunteer.yml",
      $this->sandboxInstance . "/config/default/user.role.volunteer.yml"
    );

    // Validate status (should fail).
    $result = $this->inspector->isActiveConfigIdentical();
    $this->assertEquals(FALSE, $result);
  }

}
