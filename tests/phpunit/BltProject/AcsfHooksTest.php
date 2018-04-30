<?php

namespace Acquia\Blt\Tests\Blt;

use Acquia\Blt\Tests\BltProjectTestBase;

/**
 * Class AcsfHooksTests.
 *
 * Verifies that acsf support has been initialized.
 */
class AcsfHooksTest extends BltProjectTestBase {

  protected $acsfSitesDataFileDestination;
  protected $acsfSitesDataFileSource;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $_ENV['AH_SITE_GROUP'] = 'blt';
    $_ENV['AH_SITE_ENVIRONMENT'] = 'test';
    $this->acsfSitesDataFileDestination = "/mnt/files/{$_ENV['AH_SITE_GROUP']}.{$_ENV['AH_SITE_ENVIRONMENT']}/files-private/sites.json";
    $this->acsfSitesDataFileSource = __DIR__ . '/../fixtures/acsf-sites.json';
  }

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
    $this->fs->copy($this->acsfSitesDataFileSource, $this->acsfSitesDataFileDestination);
    list($status_code, $output, $config) = $this->blt("artifact:acsf-hooks:db-update", [
      'site' => 's1',
      'target_env' => 'dev',
      // @todo Add values!
      'db_role' => '',
      'domain' => '',
    ]);
    $this->assertEquals(0, $status_code);

    // @todo Implement tests for the following factory hooks.
    // post-install/post-install.php.
    // post-settings-php/includes.php.
    // clear-twig-cache.sh.
    // pre-settings-php/includes.php.
  }

  protected function tearDown() {
    $this->fs->remove($this->acsfSitesDataFileDestination);
  }

}
