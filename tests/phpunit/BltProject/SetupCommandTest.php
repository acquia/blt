<?php

namespace Acquia\Blt\Tests\Blt;

use Acquia\Blt\Tests\BltProjectTestBase;

/**
 * Class SetupCommandTest.
 */
class SetupCommandTest extends BltProjectTestBase {

  public function testNoConfig() {
    $this->blt("setup", [
      '--define' => [
        'setup.strategy=none',
      ],
    ]);
  }

  public function testSqlImport() {
    $this->createDatabaseDumpFixture();
    $this->blt("setup", [
      '--define' => [
        'setup.strategy=import',
        'setup.dump-file=' . $this->dbDump,
      ],
    ]);
  }

  // Sync strategy is tested is MultisiteTest.php.
}
