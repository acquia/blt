<?php

namespace Acquia\Blt\Tests\Blt;

use Acquia\Blt\Tests\BltProjectTestBase;

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
  }

  // Sync strategy is tested is MultisiteTest.php.
}
