<?php

namespace Acquia\Blt\Tests\BltProject;

use Acquia\Blt\Robo\Common\YamlMunge;
use Acquia\Blt\Tests\BltProjectTestBase;

/**
 * Class DrupalSettingsTest.
 */
class DrupalSettingsTest extends BltProjectTestBase {

  /**
   * Tests blt:init:settings command.
   *
   * This command should have been run during sandbox master creation.
   */
  public function testSetupDefaultLocalSettings() {
    $sites = $this->config->get("multisites");

    foreach ($sites as $site) {
      $this->assertFileExists("$this->sandboxInstance/docroot/sites/$site/settings/default.local.settings.php");
      $this->assertFileExists("$this->sandboxInstance/docroot/sites/$site/default.settings.php");
      $this->assertFileExists("$this->sandboxInstance/docroot/sites/$site/settings/local.settings.php");

      $this->assertContains('${drupal.db.database}', file_get_contents("$this->sandboxInstance/docroot/sites/$site/settings/default.local.settings.php"));
      $this->assertContains($this->config->get("drupal.db.database"), file_get_contents("$this->sandboxInstance/docroot/sites/$site/settings/local.settings.php"));
      $this->assertNotContains('${drupal.db.database}', file_get_contents("$this->sandboxInstance/docroot/sites/$site/settings/local.settings.php"));

      $this->assertFileExists("$this->sandboxInstance/docroot/sites/$site/settings.php");

      $this->assertContains(
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
