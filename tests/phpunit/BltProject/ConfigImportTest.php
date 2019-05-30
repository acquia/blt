<?php

namespace Acquia\Blt\Tests\BltProject;

use Acquia\Blt\Tests\BltProjectTestBase;

/**
 * Class ConfigImportTest.
 *
 * @group orca_ignore
 */
class ConfigImportTest extends BltProjectTestBase {

  public function setUp() {
    parent::setUp();
    $this->importDbFromFixture();
  }

  /**
   * @group requires-db
   */
  public function testNoConfig() {
    $this->drush("config-export --yes");
    list($status_code, $output, $config) = $this->blt("drupal:config:import", [
      '--define' => [
        'cm.strategy=none',
      ],
    ]);
    $this->assertEquals(0, $status_code);
  }

  /**
   * @group requires-db
   */
  public function testFeatures() {
    $this->drush("pm-enable features --yes");
    $this->drush("config-export --yes");
    list($status_code, $output, $config) = $this->blt("drupal:config:import", [
      '--define' => [
        'cm.strategy=features',
      ],
    ]);
    $this->assertEquals(0, $status_code);
  }

  /**
   * @group requires-db
   */
  public function testCoreOnly() {
    $this->drush("config-export --yes");
    list($status_code, $output, $config) = $this->blt("drupal:config:import", [
      '--define' => [
        'cm.strategy=core-only',
      ],
    ]);
    $this->assertEquals(0, $status_code);
  }

  /**
   * @group requires-db
   */
  public function testConfigSplit() {
    $this->drush("pm-enable config_split --yes");
    $this->drush("config-export --yes");
    $this->fs->copy(
      $this->bltDirectory . "/scripts/blt/ci/internal/config_split.config_split.ci.yml",
      $this->sandboxInstance . "/config/default/config_split.config_split.ci.yml"
    );
    list($status_code, $output, $config) = $this->blt("drupal:config:import", [
      '--define' => [
        'cm.strategy=config-split',
      ],
    ]);
    $this->assertEquals(0, $status_code);
  }

}
