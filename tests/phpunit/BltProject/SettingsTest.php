<?php

namespace Acquia\Blt\Tests\BltProject;

use Acquia\Blt\Tests\BltProjectTestBase;

/**
 * Class SettingsTest.
 *
 * Verifies $settings.php configuration.
 */
class SettingsTest extends BltProjectTestBase {

  /**
   * Tests Phing setup:drupal:settings target.
   *
   * @group blt-project
   */
  public function testSetupLocalSettings() {
    $this->assertFileExists($this->projectDirectory . '/docroot/sites/default/settings/local.settings.php');
  }

}
