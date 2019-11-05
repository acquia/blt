<?php

namespace Acquia\Blt\Tests\BltProject;

use Acquia\Blt\Tests\BltProjectTestBase;

/**
 * Class AcsfHooksTests.
 *
 * Verifies that acsf support has been initialized.
 *
 * @group orca_ignore
 */
class AcsfHooksTest extends BltProjectTestBase {

  /**
   * Tests recipes:acsf:init:all command.
   */
  public function testAcsfInit() {
    list($status_code) = $this->blt("recipes:acsf:init:all");
    $this->assertEquals(0, $status_code);
    $this->assertFileExists($this->sandboxInstance . '/docroot/modules/contrib/acsf');
    $this->assertFileExists($this->sandboxInstance . '/factory-hooks');
    list($status_code) = $this->blt("tests:acsf:validate");
    $this->assertEquals(0, $status_code);

    $this->installDrupalMinimal();

    // Mimics factory-hooks/db-update/db-update.sh.
    list($status_code) = $this->blt("artifact:acsf-hooks:db-update", [
      'site' => 'blt',
      'target_env' => '01dev',
      'db_role' => 'blt123dev',
      'domain' => 'blted1.dev-blt.acsitefactory.com',
    ]);
    $this->assertEquals(0, $status_code);

    // @todo Implement tests for the following factory hooks.
    // post-install/post-install.php.
    // post-settings-php/includes.php.
    // clear-twig-cache.sh.
    // pre-settings-php/includes.php.
  }

}
