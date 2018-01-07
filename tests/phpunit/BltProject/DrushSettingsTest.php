<?php

namespace Acquia\Blt\Tests\BltProject;

use Acquia\Blt\Tests\BltProjectTestBase;

/**
 * Class DrushSettingsTest.
 *
 * Verifies Drush configuration.
 */
class DrushSettingsTest extends BltProjectTestBase {

  /**
   * Tests setup:drush:settings command.
   *
   * Ensures each site has a local.drush.yml file.
   *
   * @group blted8
   */
  public function testSetupLocalSettings() {
    foreach ($this->sites as $site) {
      $this->assertFileExists("$this->projectDirectory/docroot/sites/$site/local.drush.yml");
    }
  }

  /**
   * Tests setup:drush:settings command.
   *
   * Ensures each site has a default.local.drush.yml.
   *
   * @group blted8
   */
  public function testSetupDefaultSettings() {
    foreach ($this->sites as $site) {
      $this->assertFileExists("$this->projectDirectory/docroot/sites/$site/default.local.drush.yml");
    }
  }

}
