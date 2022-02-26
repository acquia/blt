<?php

namespace Acquia\Blt\Tests;

use Acquia\Blt\Robo\Exceptions\BltException;

/**
 * Test blt config imports.
 */
class ConfigImportTest extends BltProjectTestBase {

  /**
   * @throws \Exception
   */
  public function testNoConfig() {
    $this->drush("config-export --yes");
    list($status_code) = $this->blt("drupal:config:import", [
      '--define' => [
        'cm.strategy=none',
      ],
    ]);
    $this::assertEquals(0, $status_code);
  }

  /**
   * @throws \Exception
   */
  public function testNoConfigException() {
    $this->drush("config-export --yes");
    list($status_code) = $this->blt("drupal:config:import", [
      '--define' => [
        'cm.strategy=no',
      ],
    ]);
    //$this::assertEquals(0, $status_code);
    $this::expectException(BltException::class);
    //$this->expectExceptionMessage('Expected Exception Message');
  }

  /**
   * @throws \Exception
   */
  public function testCoreOnly() {
    $this->drush("config-export --yes");
    list($status_code) = $this->blt("drupal:config:import", [
      '--define' => [
        'cm.strategy=core-only',
      ],
    ]);
    static::assertEquals(0, $status_code);
  }

  /**
   * @todo re-enable after Config Split D9 release.
   *
   * @group orca_ignore
   *
   * @throws \Exception
   */
  public function testConfigSplit() {
    $this->drush("pm-enable config_split --yes");
    $this->drush("config-export --yes");
    $this->fs->copy(
      $this->bltDirectory . "/scripts/blt/ci/internal/config_split.config_split.ci.yml",
      $this->sandboxInstance . "/config/default/config_split.config_split.ci.yml"
    );
    list($status_code) = $this->blt("drupal:config:import", [
      '--define' => [
        'cm.strategy=config-split',
      ],
    ]);
    static::assertEquals(0, $status_code);
  }

}
