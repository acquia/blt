<?php

namespace Acquia\Blt\Tests;

/**
 * Tests Drupal settings.
 */
class DrupalSettingsTest extends BltProjectTestBase {

  /**
   * Tests blt:init:settings command.
   */
  public function testSetupDefaultLocalSettings() {
    $this->blt('blt:init:settings');
    $sites = $this->config->get("multisites");

    $this->assertFileExists("$this->sandboxInstance/docroot/sites/default/default.settings.php");
    foreach ($sites as $site) {
      $this->assertFileExists("$this->sandboxInstance/docroot/sites/$site/settings/default.local.settings.php");
      $this->assertFileExists("$this->sandboxInstance/docroot/sites/$site/settings/local.settings.php");

      $this->assertStringContainsString('${drupal.db.database}', file_get_contents("$this->sandboxInstance/docroot/sites/$site/settings/default.local.settings.php"));
      $this->assertStringContainsString($this->config->get("drupal.db.database"), file_get_contents("$this->sandboxInstance/docroot/sites/$site/settings/local.settings.php"));
      $this->assertStringNotContainsString('${drupal.db.database}', file_get_contents("$this->sandboxInstance/docroot/sites/$site/settings/local.settings.php"));

      $this->assertFileExists("$this->sandboxInstance/docroot/sites/$site/settings.php");

      $this->assertStringContainsString(
        'require DRUPAL_ROOT . "/../vendor/acquia/drupal-recommended-settings/settings/acquia-recommended.settings.php"',
        file_get_contents("$this->sandboxInstance/docroot/sites/$site/settings.php")
      );

      $this->assertFileExists("$this->sandboxInstance/docroot/sites/$site/local.drush.yml");
      $this->assertFileExists("$this->sandboxInstance/docroot/sites/$site/default.local.drush.yml");

      $this->assertFileExists("$this->sandboxInstance/blt/blt.yml");
      $this->assertFileExists("$this->sandboxInstance/blt/ci.blt.yml");
    }
  }

}
