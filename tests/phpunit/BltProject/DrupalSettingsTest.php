<?php

namespace Acquia\Blt\Tests\BltProject;

use Acquia\Blt\Tests\BltProjectTestBase;

/**
 * Class DrupalSettingsTest.
 *
 * Verifies $settings.php configuration.
 */
class DrupalSettingsTest extends BltProjectTestBase {

  /**
   * Tests setup:drupal:settings command.
   *
   * Ensures each site has a default.local.settings.php file.
   *
   * @group blt-project
   */
  public function testSetupDefaultLocalSettings() {
    foreach ($this->sites as $site) {
      $this->assertFileExists("$this->projectDirectory/docroot/sites/$site/settings/default.local.settings.php");
    }
  }

  /**
   * Tests setup:drupal:settings command.
   *
   * Ensures each site has a local.settings.php file.
   *
   * @group blt-project
   */
  public function testSetupLocalSettings() {
    foreach ($this->sites as $site) {
      $this->assertFileExists("$this->projectDirectory/docroot/sites/$site/settings/local.settings.php");
    }
  }

  /**
   * Tests setup:drupal:settings command.
   *
   * Ensures the default site has a default.settings.php file, which is used
   * as the basis for new settings.php files.
   *
   * @group blt-project
   */
  public function testSetupDefaultSettings() {
    $this->assertFileExists("$this->projectDirectory/docroot/sites/default/default.settings.php");
  }

  /**
   * Tests setup:drupal:settings command.
   *
   * Ensures each site has a settings.php file.
   *
   * @group blt-project
   */
  public function testSetupSettings() {
    foreach ($this->sites as $site) {
      $this->assertFileExists("$this->projectDirectory/docroot/sites/$site/settings.php");
    }
  }

  /**
   * Tests setup:drupal:settings command.
   *
   * Ensures BLT's settings are included in each site's settings.php file.
   *
   * @group blt-project
   */
  public function testSetupBltSettings() {
    foreach ($this->sites as $site) {
      $file = "$this->projectDirectory/docroot/sites/$site/settings.php";
      if (file_exists($file)) {
        $this->assertContains(
          'require DRUPAL_ROOT . "/../vendor/acquia/blt/settings/blt.settings.php"',
          file_get_contents($file)
        );
      }
    }
  }

}
