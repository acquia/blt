<?php

namespace Acquia\Blt\Tests;

/**
 * Test blt setup.
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
    $this->executor->drush(["config-export", "--yes"])->run();
    $this->executor->drush(["sql-drop", "--yes"])->run();
    [$status_code] = $this->installDrupalMinimal();
    $this->assertEquals(0, $status_code);
  }

  /**
   * Asserts that the deployment_identifier file exists and is not empty.
   */
  protected function assertDeploymentIdentifierSetupValidity() {
    $this->assertFileExists($this->config->get('repo.root') . '/deployment_identifier');
    $this->assertNotEmpty(file_get_contents($this->config->get('repo.root') . '/deployment_identifier'));
  }

  /**
   * @throws \Exception
   */
  public function testSyncStrategy() {
    $this->expectException('Exception');
    $this->blt("setup", [
      '--define' => [
        'setup.strategy=sync',
        'project.profile.name=minimal',
      ],
    ]);
  }

  /**
   * @throws \Exception
   */
  public function testImportStrategy() {
    $this->expectException('Exception');
    $this->blt("setup", [
      '--define' => [
        'setup.strategy=import',
        'project.profile.name=minimal',
      ],
    ]);
  }

  // Sync strategy is tested is MultisiteTest.php.
}
