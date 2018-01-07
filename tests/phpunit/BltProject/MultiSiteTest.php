<?php

namespace Acquia\Blt\Tests\Blt;

use Acquia\Blt\Tests\BltProjectTestBase;
use function file_get_contents;

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
    $this->assertFileExists($this->projectDirectory . '/docroot/sites/site2');
    $this->assertFileExists($this->projectDirectory . '/docroot/sites/site2/site.yml');
    $this->assertContains('local.site2.com', file_get_contents($this->projectDirectory . '/docroot/sites/site2/site.yml'));
    $this->assertFileExists($this->projectDirectory . '/docroot/sites/site2/settings.php');
    $this->assertFileExists($this->projectDirectory . '/docroot/sites/site2/default.settings.php');
    $this->assertFileExists($this->projectDirectory . '/docroot/sites/site2/local.drush.yml');
    $this->assertFileExists($this->projectDirectory . '/docroot/sites/site2/default.local.drush.yml');
    $this->assertFileExists($this->projectDirectory . '/docroot/sites/site2/settings');
    $this->assertFileExists($this->projectDirectory . '/docroot/sites/site2/settings/default.local.settings.php');
    $this->assertNotContains('${drupal.db.database}', file_get_contents($this->projectDirectory . '/docroot/sites/site2/settings/local.settings.php'));
    $this->assertFileExists($this->projectDirectory . '/docroot/sites/site2/settings/local.settings.php');
    $this->assertFileExists($this->projectDirectory . '/config/site2');
    // @todo Test that -D site=x sets uri and other config.
  }

}
