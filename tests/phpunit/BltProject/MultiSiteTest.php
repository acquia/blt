<?php

namespace Acquia\Blt\Tests\Blt;

use Acquia\Blt\Tests\BltProjectTestBase;

/**
 * Class MultiSiteTest.
 *
 * @group requires-db
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

    // Clone.
    $this->assertFileNotExists("$this->sandboxInstanceClone/docroot/sites/$this->site1Dir/settings/local.settings.php");
    $this->assertFileNotExists("$this->sandboxInstanceClone/docroot/sites/$this->site2Dir/settings/local.settings.php");
    $this->assertFileNotExists("$this->sandboxInstanceClone/docroot/sites/$this->site1Dir/local.drush.yml");
    $this->assertFileNotExists("$this->sandboxInstanceClone/docroot/sites/$this->site2Dir/local.drush.yml");
    $this->assertFileExists("$this->sandboxInstanceClone/docroot/sites/$this->site1Dir/blt.yml");
    $this->assertFileExists("$this->sandboxInstanceClone/docroot/sites/$this->site2Dir/blt.yml");
    $this->assertFileExists("$this->sandboxInstanceClone/docroot/sites/sites.php");
    $this->assertFileExists($this->sandboxInstanceClone . '/config/site2');

    // We cannot use $this->blt because we are not executing in sandbox.
    $this->execute('./vendor/bin/blt setup:settings', $this->sandboxInstanceClone);

    // Generate fixture.
    // Sets up site1 locally too.
    $this->createDatabaseDumpFixture();
    // Set up site 2.
    list($status_code, $output, $config) = $this->blt("setup", [
      '--define' => [
        'project.profile.name=minimal',
      ],
      '--site' => 'site2',
      '--yes' => '',
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
    // Site2 is not installed!
    $this->assertEquals('Site 2 Local', $output_array['name']);

    $output_array = $this->drushJson("@default.clone config:get system.site");
    $this->assertEquals('Site 1 Clone', $output_array['name']);

    $output_array = $this->drushJson("@site2.clone config:get system.site");
    $this->assertEquals('Site 2 Clone', $output_array['name']);

    list($status_code, $output, $config) = $this->blt("sync:db:all", [
      '--yes' => '',
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

}
