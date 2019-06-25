<?php

namespace Acquia\Blt\Tests\BltProject;

use Acquia\Blt\Robo\Common\YamlMunge;
use Acquia\Blt\Tests\BltProjectTestBase;
use Symfony\Component\Yaml\Yaml;

/**
 * Class MultiSiteTest.
 *
 * @group requires-db
 * @group orca_ignore
 */
class MultiSiteTest extends BltProjectTestBase {

  /**
   * Tests multisite.
   */
  public function testMultisiteGenerate() {
    $this->prepareMultisites($this->site1Dir, $this->site2Dir, $this->sandboxInstance, $this->sandboxInstanceClone);

    // Make sure we prepared thing correctly.
    // Local.
    $this->assertFileNotExists("$this->sandboxInstance/docroot/sites/$this->site1Dir/settings/local.settings.php");
    $this->assertFileNotExists("$this->sandboxInstance/docroot/sites/$this->site2Dir/settings/local.settings.php");
    $this->assertFileNotExists("$this->sandboxInstance/docroot/sites/$this->site1Dir/local.drush.yml");
    $this->assertFileNotExists("$this->sandboxInstance/docroot/sites/$this->site2Dir/local.drush.yml");
    $this->assertFileExists("$this->sandboxInstance/docroot/sites/$this->site1Dir/blt.yml");
    $this->assertFileExists("$this->sandboxInstance/docroot/sites/$this->site2Dir/blt.yml");
    $this->assertFileExists("$this->sandboxInstance/docroot/sites/sites.php");
    $this->assertFileExists($this->sandboxInstance . '/config/site2');
    $this->assertFileExists($this->sandboxInstance . "/drush/sites/$this->site1Dir.site.yml");
    $this->assertFileExists($this->sandboxInstance . "/drush/sites/$this->site2Dir.site.yml");

    $site1_alias = YamlMunge::parseFile($this->sandboxInstance . "/drush/sites/$this->site1Dir.site.yml");
    $this->assertEquals($this->site1Dir, $site1_alias['local']['uri']);

    $site2_alias = YamlMunge::parseFile($this->sandboxInstance . "/drush/sites/$this->site2Dir.site.yml");
    $this->assertEquals($this->site2Dir, $site2_alias['local']['uri']);

    $site1_blt_yml = YamlMunge::parseFile("$this->sandboxInstance/docroot/sites/$this->site1Dir/blt.yml");
    $this->assertEquals("self", $site1_blt_yml['drush']['aliases']['local']);
    $this->assertEquals("$this->site1Dir.clone", $site1_blt_yml['drush']['aliases']['remote']);

    $site2_blt_yml = YamlMunge::parseFile("$this->sandboxInstance/docroot/sites/$this->site2Dir/blt.yml");
    $this->assertEquals("site2.local", $site2_blt_yml['drush']['aliases']['local']);
    $this->assertEquals("$this->site2Dir.clone", $site2_blt_yml['drush']['aliases']['remote']);

    // Clone.
    $this->assertFileNotExists("$this->sandboxInstanceClone/docroot/sites/$this->site1Dir/settings/local.settings.php");
    $this->assertFileNotExists("$this->sandboxInstanceClone/docroot/sites/$this->site2Dir/settings/local.settings.php");
    $this->assertFileNotExists("$this->sandboxInstanceClone/docroot/sites/$this->site1Dir/local.drush.yml");
    $this->assertFileNotExists("$this->sandboxInstanceClone/docroot/sites/$this->site2Dir/local.drush.yml");
    $this->assertFileExists("$this->sandboxInstanceClone/docroot/sites/$this->site1Dir/blt.yml");
    $this->assertFileExists("$this->sandboxInstanceClone/docroot/sites/$this->site2Dir/blt.yml");
    $this->assertFileExists("$this->sandboxInstanceClone/docroot/sites/sites.php");
    $this->assertFileExists($this->sandboxInstanceClone . '/config/site2');

    // Generate local.setting.php, copy to ci.settings.php since
    // local.settings.php is not loaded in CI env.
    $this->blt("blt:init:settings");
    $this->fs->copy(
      "$this->sandboxInstance/docroot/sites/$this->site1Dir/settings/local.settings.php",
      "$this->sandboxInstance/docroot/sites/$this->site1Dir/settings/ci.settings.php"
    );
    $this->fs->copy(
      "$this->sandboxInstance/docroot/sites/$this->site2Dir/settings/local.settings.php",
      "$this->sandboxInstance/docroot/sites/$this->site2Dir/settings/ci.settings.php"
    );

    // Generate local.setting.php, copy to ci.settings.php since
    // local.settings.php is not loaded in CI env.
    // We cannot use $this->blt because we are not executing in sandbox.
    $this->execute('./vendor/bin/blt blt:init:settings', $this->sandboxInstanceClone);
    $this->fs->copy(
      "$this->sandboxInstanceClone/docroot/sites/$this->site1Dir/settings/local.settings.php",
      "$this->sandboxInstanceClone/docroot/sites/$this->site1Dir/settings/ci.settings.php"
    );
    $this->fs->copy(
      "$this->sandboxInstanceClone/docroot/sites/$this->site2Dir/settings/local.settings.php",
      "$this->sandboxInstanceClone/docroot/sites/$this->site2Dir/settings/ci.settings.php"
    );

    // Generate fixture.
    // Sets up site1 locally too.
    $this->createDatabaseDumpFixture();
    // Set up site 2.
    $this->blt("setup", [
      '--define' => [
        'project.profile.name=minimal',
      ],
      '--site' => 'site2',
      '--no-interaction' => '',
    ]);

    // Assert setup.
    $this->assertFileExists("$this->sandboxInstance/docroot/sites/$this->site1Dir/settings/local.settings.php");
    $this->assertFileExists("$this->sandboxInstance/docroot/sites/$this->site2Dir/settings/local.settings.php");
    $this->assertFileExists("$this->sandboxInstance/docroot/sites/$this->site1Dir/local.drush.yml");
    $this->assertFileExists("$this->sandboxInstance/docroot/sites/$this->site2Dir/local.drush.yml");
    $this->assertNotContains('${drupal.db.database}', file_get_contents("$this->sandboxInstance/docroot/sites/$this->site1Dir/settings/local.settings.php"));

    // Setup Site1 clone.
    $this->importDbFromFixture($this->sandboxInstanceClone, $this->site1Dir);
    $this->drush("config:set system.site name 'Site 1 Clone' --yes --uri=$this->site1Dir", $this->sandboxInstanceClone);
    // Setup Site2 clone.
    $this->importDbFromFixture($this->sandboxInstanceClone, $this->site2Dir);
    $this->drush("config:set system.site name 'Site 2 Clone' --yes --uri=$this->site2Dir", $this->sandboxInstanceClone);

    // Assert setup.
    $this->assertFileExists("$this->sandboxInstanceClone/docroot/sites/$this->site1Dir/settings/local.settings.php");
    $this->assertFileExists("$this->sandboxInstanceClone/docroot/sites/$this->site2Dir/settings/local.settings.php");
    $this->assertFileExists("$this->sandboxInstanceClone/docroot/sites/$this->site1Dir/local.drush.yml");
    $this->assertFileExists("$this->sandboxInstanceClone/docroot/sites/$this->site2Dir/local.drush.yml");

    $output_array = $this->drushJson("@default.local config:get system.site");
    $this->assertEquals('Site 1 Local', $output_array['name']);

    $output_array = $this->drushJson("@site2.local config:get system.site");

    $this->assertEquals('Site 2 Local', $output_array['name']);

    $output_array = $this->drushJson("@default.clone config:get system.site");
    $this->assertEquals('Site 1 Clone', $output_array['name']);

    $output_array = $this->drushJson("@site2.clone config:get system.site");
    $this->assertEquals('Site 2 Clone', $output_array['name']);

    list(, $output,) = $this->blt("drupal:sync:all-sites", [
      '--no-interaction' => '',
    ]);
    $this->assertContains("You will destroy data in drupal and replace with data from drupal3", $output);
    $this->assertContains("You will destroy data in drupal2 and replace with data from drupal4", $output);

    $output_array = $this->drushJson("@default.local config-get system.site");
    $this->assertEquals('Site 1 Clone', $output_array['name']);

    $output_array = $this->drushJson("@site2.local config-get system.site");
    $this->assertEquals('Site 2 Clone', $output_array['name']);

    $output_array = $this->drushJson("@default.clone config-get system.site");
    $this->assertEquals('Site 1 Clone', $output_array['name']);

    $output_array = $this->drushJson("@site2.clone config-get system.site");
    $this->assertEquals('Site 2 Clone', $output_array['name']);
  }

  /**
   * Test VM-specific functionality.
   *
   * @throws \Exception
   */
  public function testMultisiteVmGenerate() {
    // Test that VM config gets updated correctly with new multisite info.
    $this->blt('vm', ['--no-boot' => TRUE]);
    $expected_yaml = Yaml::parse(file_get_contents("$this->sandboxInstance/box/config.yml"));
    $expected_yaml['apache_vhosts'][] = [
      'servername' => 'local.foo.com',
      'documentroot' => '{{ drupal_core_path }}',
      'extra_parameters' => '{{ apache_vhost_php_fpm_parameters }}',
    ];
    $this->blt('multisite', ['--site-dir' => 'foo']);
    $new_yaml = Yaml::parse(file_get_contents("$this->sandboxInstance/box/config.yml"));
    $this::assertEquals($expected_yaml, $new_yaml);
  }

}
