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
   * Tests Phing setup:drush:settings target.
   *
   * Ensures each site has a local.drushrc.php file.
   *
   * @group blt-project
   */
  public function testSetupLocalSettings() {
    foreach ($this->sites as $site) {
      $this->assertFileExists("$this->projectDirectory/docroot/sites/$site/local.drushrc.php");
    }
  }

  /**
   * Tests Phing setup:drush:settings target.
   *
   * Ensures each site has a default.local.drushrc.php.
   *
   * @group blt-project
   */
  public function testSetupDefaultSettings() {
    foreach ($this->sites as $site) {
      $this->assertFileExists("$this->projectDirectory/docroot/sites/$site/default.local.drushrc.php");
    }
  }

}
