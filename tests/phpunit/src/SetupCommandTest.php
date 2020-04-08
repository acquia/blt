<?php

namespace Acquia\Blt\Tests;

/**
 * Class SetupCommandTest.
 */
class SetupCommandTest extends BltProjectTestBase {

  public function testInstallStrategy() {
    $this->blt("setup", [
      '--define' => [
        'setup.strategy=install',
        'project.profile.name=minimal',
      ],
    ]);
    $this->assertDeploymentIdentifierSetupValidity();
  }

  /**
   * Test that config import when exported system UUID != installed UUID.
   */
  public function testChangedUuid() {
    $this->drush("config-export --yes");
    $this->drush("sql-drop --yes");
    list($status_code) = $this->installDrupalMinimal();
    $this->assertEquals(0, $status_code);
  }

  /**
   * Asserts that the deployment_identifier file exists and is not empty.
   */
  protected function assertDeploymentIdentifierSetupValidity() {
    $this->assertFileExists($this->config->get('repo.root') . '/deployment_identifier');
    $this->assertNotEmpty(file_get_contents($this->config->get('repo.root') . '/deployment_identifier'));
  }

  // Sync strategy is tested is MultisiteTest.php.
}
