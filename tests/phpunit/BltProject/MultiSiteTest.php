<?php

namespace Acquia\Blt\Tests\Blt;

use Acquia\Blt\Tests\BltProjectTestBase;

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
    // @codingStandardsIgnoreStart
    // $site2_config = ArrayManipulator::flattenToDotNotatedKeys(YamlMunge::parseFile($site_dir . '/blt.site.yml'));
    // $this->assertContains('local.blted8.site2.com', $site2_config);
    // $this->assertContains('drupal2', $site2_config);
    // $this->assertContains('site2', $site2_config);
    // @codingStandardsIgnoreEnd
    $this->assertFileExists($site_dir . '/settings.php');
    $this->assertFileExists($site_dir . '/default.settings.php');
    $this->assertFileExists($site_dir . '/local.drush.yml');
    $this->assertFileExists($site_dir . '/default.local.drush.yml');
    $this->assertFileExists($site_dir . '/settings');
    $this->assertFileExists($site_dir . '/settings/default.local.settings.php');
    $this->assertNotContains('${drupal.db.database}', file_get_contents($site_dir . '/settings/local.settings.php'));
    $this->assertFileExists($site_dir . '/settings/local.settings.php');
    $this->assertFileExists($this->projectDirectory . '/config/site2');
    // @todo Test that -D site=x sets uri and other config.
  }

}
