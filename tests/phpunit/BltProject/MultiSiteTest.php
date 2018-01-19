<?php

namespace Acquia\Blt\Tests\Blt;

use Acquia\Blt\Robo\Common\YamlMunge;
use Acquia\Blt\Tests\BltProjectTestBase;

/**
 * Class MultiSiteTest.
 */
class MultiSiteTest extends BltProjectTestBase {

  /**
   * Tests multisite.
   */
  public function testMultisiteGenerate() {
    // Set site dirs.
    $site1_dir = 'default';
    $site2_dir = 'site2';
    $test_project_dir = $this->sandboxInstance;
    $test_project_clone_dir = $test_project_dir . "2";

    $this->prepareMultisites($site1_dir, $site2_dir, $test_project_dir, $test_project_clone_dir);

    // Generate fixture. Sets up site1 locally too.
    $this->createDatabaseDumpFixture();
    list($status_code, $output) = $this->blt("setup", [
      '--define' => [
        'project.profile.name=minimal',
      ],
      '--site' => 'site2',
      '--yes' => '',
    ]);

    $this->importDbFromFixture($test_project_clone_dir, $site1_dir);
    $this->drush("config:set system.site name 'Site 1 Clone' --yes --uri=$site1_dir", $test_project_clone_dir);
    $this->importDbFromFixture($test_project_clone_dir, $site2_dir);
    $this->drush("config:set system.site name 'Site 2 Clone' --yes --uri=$site2_dir", $test_project_clone_dir);

    $site_dir = $this->sandboxInstance . '/docroot/sites/site2';
    $this->assertFileExists($site_dir);
    $this->assertFileExists($this->sandboxInstance . '/docroot/sites/site2/blt.yml');
    $this->assertFileExists($site_dir . '/settings.php');
    $this->assertFileExists($site_dir . '/default.settings.php');
    $this->assertFileExists($site_dir . '/local.drush.yml');
    $this->assertFileExists($site_dir . '/default.local.drush.yml');
    $this->assertFileExists($site_dir . '/settings');
    $this->assertFileExists($site_dir . '/settings/default.local.settings.php');
    $this->assertNotContains('${drupal.db.database}', file_get_contents($site_dir . '/settings/local.settings.php'));
    $this->assertFileExists($site_dir . '/settings/local.settings.php');
    $this->assertFileExists($this->sandboxInstance . '/config/site2');

    $output_array = $this->drushJson("@default.local config:get system.site");
    $this->assertEquals('Site 1 Local', $output_array['name']);

    $output_array = $this->drushJson("@site2.local config:get system.site");
    // Site2 is not installed!
    $this->assertEquals('Site 2 Local', $output_array['name']);

    $output_array = $this->drushJson("@default.clone config:get system.site");
    $this->assertEquals('Site 1 Clone', $output_array['name']);

    $output_array = $this->drushJson("@site2.clone config:get system.site");
    $this->assertEquals('Site 2 Clone', $output_array['name']);

    list($status_code, $output) = $this->blt("sync:db:all", [
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

  protected function prepareMultisites($site1_dir, $site2_dir, $test_project_dir, $test_project_clone_dir) {
    // Set test project vars.
    $site1_local_uri = 'local.blted8.site1.com';
    $site2_local_uri = 'local.blted8.site2.com';
    $site1_local_db_name = 'drupal';
    $site2_local_db_name = 'drupal2';
    $site1_local_human_name = "Site 1 Local";
    $site2_local_human_name = "Site 2 Local";
    $site1_remote_drush_alias = "$site1_dir.clone";
    $site2_remote_drush_alias = "$site2_dir.clone";

    // Create test project clone vars.
    $site1_clone_uri = 'local.blted82.site1.com';
    $site2_clone_uri = 'local.blted82.site2.com';
    $site1_clone_db_name = 'drupal3';
    $site2_clone_db_name = 'drupal4';
    $site1_clone_human_name = "Site 1 Clone";
    $site2_clone_human_name = "Site 2 Clone";
    $this->blt("generate:multisite", [
      '--site-dir' => $site2_dir,
      '--site-uri' => "http://" . $site2_local_uri,
      '--remote-alias' => $site2_remote_drush_alias,
      '--yes' => '',
    ]);

    $this->fs->mirror($test_project_dir, $test_project_clone_dir);

    // Create drush alias for site1.
    $aliases['clone'] = [
      'root' => $test_project_clone_dir,
      'uri' => $site1_dir,
    ];
    YamlMunge::mergeArrayIntoFile($aliases,
      "$test_project_dir/drush/sites/$site1_dir.site.yml");

    // Create drush alias for site2.
    $aliases['clone'] = [
      'root' => $test_project_clone_dir,
      'uri' => $site2_dir,
    ];
    YamlMunge::mergeArrayIntoFile($aliases,
      "$test_project_dir/drush/sites/$site2_dir.site.yml");

    // Site 1 local.
    $project_yml = [];
    $project_yml['project']['local']['hostname'] = $site1_local_uri;
    $project_yml['project']['human_name'] = $site1_local_human_name;
    $project_yml['drupal']['db']['database'] = $site1_local_db_name;
    $project_yml['drush']['aliases']['remote'] = $site1_remote_drush_alias;
    YamlMunge::mergeArrayIntoFile($project_yml,
      $test_project_dir . "/docroot/sites/$site1_dir/blt.yml");

    // Site 2 local.
    $project_yml = [];
    $project_yml['project']['human_name'] = $site2_local_human_name;
    $project_yml['drupal']['db']['database'] = $site2_local_db_name;
    // drush.aliases.remote should already have been set via generate command.
    YamlMunge::mergeArrayIntoFile($project_yml,
      $test_project_dir . "/docroot/sites/$site2_dir/blt.yml");

    // Site 1 clone.
    $project_yml = [];
    $project_yml['project']['human_name'] = $site1_clone_human_name;
    $project_yml['drupal']['db']['database'] = $site1_clone_db_name;
    YamlMunge::mergeArrayIntoFile($project_yml,
      $test_project_clone_dir . "/docroot/sites/$site1_dir/blt.yml");

    // Site 2 clone.
    $project_yml = [];
    $project_yml['project']['human_name'] = $site2_clone_human_name;
    $project_yml['drupal']['db']['database'] = $site2_clone_db_name;
    YamlMunge::mergeArrayIntoFile($project_yml,
      $test_project_clone_dir . "/docroot/sites/$site2_dir/blt.yml");

    // Generate sites.php for local app.
    $sites[$site1_local_uri] = $site1_dir;
    $sites[$site2_local_uri] = $site2_dir;
    $contents = "<?php\n \$sites = " . var_export($sites, TRUE) . ";";
    file_put_contents($test_project_dir . "/docroot/sites/sites.php",
      $contents);

    // Generate sites.php for clone app.
    $sites[$site1_clone_uri] = $site1_dir;
    $sites[$site2_clone_uri] = $site2_dir;
    $contents = "<?php\n \$sites = " . var_export($sites, TRUE) . ";";
    file_put_contents($test_project_clone_dir . "/docroot/sites/sites.php",
      $contents);

    // Delete local.settings.php files so they can be regenerated with new
    // values in blt.yml files.
    $this->fs->remove([
      "$test_project_dir/docroot/sites/$site1_dir/settings/local.settings.php",
      "$test_project_dir/docroot/sites/$site2_dir/settings/local.settings.php",
      "$test_project_dir/docroot/sites/$site1_dir/local.drush.yml",
      "$test_project_clone_dir/docroot/sites/$site1_dir/settings/local.settings.php",
      "$test_project_clone_dir/docroot/sites/$site2_dir/settings/local.settings.php",
      "$test_project_clone_dir/docroot/sites/$site1_dir/local.drush.yml",
    ]);

    // We cannot use $this->blt because we are not executing in sandbox.
    $this->execute('./vendor/bin/blt setup:settings', $test_project_clone_dir);
  }

  public function tearDown() {
    $this->sandboxManager->removeSandboxInstance();
    parent::tearDown();
  }

}
