<?php

namespace Acquia\Blt\Tests;

use Acquia\Blt\Robo\Common\YamlMunge;

/**
 * Class DrupalSettingsTest.
 */
class DrupalSettingsTest extends BltProjectTestBase {

  /**
   * Tests blt:init:settings command.
   */
  public function testSetupDefaultLocalSettings() {
    $this->blt('blt:init:settings');
    $sites = $this->config->get("multisites");

    foreach ($sites as $site) {
      $this->assertFileExists("$this->sandboxInstance/docroot/sites/$site/settings/default.local.settings.php");
      $this->assertFileExists("$this->sandboxInstance/docroot/sites/$site/default.settings.php");
      $this->assertFileExists("$this->sandboxInstance/docroot/sites/$site/settings/local.settings.php");

      $this->assertStringContainsString('${drupal.db.database}', file_get_contents("$this->sandboxInstance/docroot/sites/$site/settings/default.local.settings.php"));
      $this->assertStringContainsString($this->config->get("drupal.db.database"), file_get_contents("$this->sandboxInstance/docroot/sites/$site/settings/local.settings.php"));
      $this->assertStringNotContainsString('${drupal.db.database}', file_get_contents("$this->sandboxInstance/docroot/sites/$site/settings/local.settings.php"));

      $this->assertFileExists("$this->sandboxInstance/docroot/sites/$site/settings.php");

      $this->assertStringContainsString(
        'require DRUPAL_ROOT . "/../vendor/acquia/blt/settings/blt.settings.php"',
        file_get_contents("$this->sandboxInstance/docroot/sites/$site/settings.php")
      );

      $this->assertFileExists("$this->sandboxInstance/docroot/sites/$site/local.drush.yml");
      $this->assertFileExists("$this->sandboxInstance/docroot/sites/$site/default.local.drush.yml");

      $output_array = $this->drushJson('status');
      $this->assertEquals($output_array['uri'], $this->config->get('project.local.uri'));
      $drush_local_site_yml = YamlMunge::parseFile("$this->sandboxInstance/docroot/sites/$site/local.drush.yml");
      $this->assertEquals($output_array['uri'], $drush_local_site_yml['options']['uri']);
    }
  }

}
