<?php

namespace Acquia\Blt\Tests\Blt;

use Acquia\Blt\Tests\BltProjectTestBase;

/**
 * Class SetupCommandTest.
 *
 * @group requires-db
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

  public function testImportStrategy() {
    $this->createDatabaseDumpFixture();
    $this->dropDatabase();
    $this->blt("setup", [
      '--define' => [
        'setup.strategy=import',
        'setup.dump-file=' . $this->dbDump,
      ],
    ]);
    $this->assertDeploymentIdentifierSetupValidity();
  }

  /**
   * Test that config import when exported system UUID != installed UUID.
   *
   * @group requires-db
   */
  public function testChangedUuid() {
    $this->importDbFromFixture();
    $this->drush("config-export --yes");
    $this->drush("sql-drop --yes");
    $this->installDrupalMinimal();
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
