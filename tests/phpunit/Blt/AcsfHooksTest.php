<?php

namespace Acquia\Blt\Tests\Blt;

use Acquia\Blt\Tests\BltProjectTestBase;

/**
 * Class AcsfHooksTests.
 *
 * Verifies that acsf support has been initialized.
 */
class AcsfHooksTest extends BltProjectTestBase {

  /**
   * Tests recipes:acsf:init:all command.
   */
  public function testAcsfInit() {
    $this->blt("recipes:acsf:init:all");
    $this->assertFileExists($this->sandboxInstance . '/docroot/modules/contrib/acsf');
    $this->assertFileExists($this->sandboxInstance . '/factory-hooks');
  }

  /**
   * Tests execution of factory-hooks.
   */
  public function testAcsfHooks() {
    $this->blt("recipes:acsf:init:all");
    $this->installDrupalMinimal();

    // Mimics factory-hooks/db-update/db-update.sh.
    list($status_code, $output, $config) = $this->blt("artifact:acsf-hooks:db-update", [
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
