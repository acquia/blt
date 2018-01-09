<?php

namespace Acquia\Blt\Tests\Blt;

use Acquia\Blt\Robo\Common\ArrayManipulator;
use Acquia\Blt\Tests\BltProjectTestBase;
use Acquia\Blt\Robo\Common\YamlMunge;

/**
 * Class MultiSiteTest.
 */
class MultiSiteTest extends BltProjectTestBase {

  /**
   * Tests multisite.
   *
   * @group blted8
   */
  public function testMultisiteGenerate() {
    $site_dir = $this->projectDirectory . '/docroot/sites/site2';
    $this->assertFileExists($site_dir);
    $this->assertFileExists($this->projectDirectory . '/docroot/sites/site2/blt.site.yml');
    $site2_config = ArrayManipulator::flattenToDotNotatedKeys(YamlMunge::parseFile($site_dir . '/blt.site.yml'));
    $this->assertContains('local.blted8.site2.com', $site2_config);
    $this->assertContains('drupal2', $site2_config);
    $this->assertContains('site2', $site2_config);
    $this->assertFileExists($site_dir . '/settings.php');
    $this->assertFileExists($site_dir . '/default.settings.php');
    $this->assertFileExists($site_dir . '/local.drush.yml');
    $this->assertFileExists($site_dir . '/default.local.drush.yml');
    $this->assertFileExists($site_dir . '/settings');
    $this->assertFileExists($site_dir . '/settings/default.local.settings.php');
    $this->assertNotContains('${drupal.db.database}', file_get_contents($site_dir . '/settings/local.settings.php'));
    $this->assertFileExists($site_dir . '/settings/local.settings.php');
    $this->assertFileExists($this->projectDirectory . '/config/site2');

    $output_array = $this->drush("@default.local config-get system.site");
    $this->assertEquals('Site 1 Local', $output_array['name']);

    $output_array = $this->drush("@site2.local config-get system.site");
    $this->assertEquals('Site 2 Local', $output_array['name']);

    $output_array = $this->drush("@default.clone config-get system.site");
    $this->assertEquals('Site 1 Clone', $output_array['name']);

    $output_array = $this->drush("@site2.clone config-get system.site");
    $this->assertEquals('Site 2 Clone', $output_array['name']);
  }

  /**
   * Tests multisite, after `blt sync:db:all` execution.
   *
   * @group blted8 post-sync
   */
  public function testMultisiteSync() {
    $output_array = $this->drush("@default.local config-get system.site");
    $this->assertEquals('Site 1 Clone', $output_array['name']);

    $output_array = $this->drush("@site2.local config-get system.site");
    $this->assertEquals('Site 2 Clone', $output_array['name']);

    $output_array = $this->drush("@default.clone config-get system.site");
    $this->assertEquals('Site 1 Clone', $output_array['name']);

    $output_array = $this->drush("@site2.clone config-get system.site");
    $this->assertEquals('Site 2 Clone', $output_array['name']);
  }

}
