<?php

namespace Acquia\Blt\Tests\BltProject;

use Acquia\Blt\Tests\BltProjectTestBase;

/**
 * Class ToggleModulesTest.
 *
 * Verifies that setup:toggle-modules behaves as expected.
 */
class ToggleModulesTest extends BltProjectTestBase {

  /**
   * Verifies the modules for a given environment were enabled as expected.
   *
   * In the event no environment is specified, this test will be skipped.
   *
   * @group blt-project
   */
  public function testModulesEnabled() {
    $modules = $this->config['modules'][BLT_ENV]['enable'];
    foreach ($modules as $module) {
      $this->assertModuleEnabled($module, BLT_ALIAS);
    }
  }

  /**
   * Verifies the modules for a given environment were disabled or not found.
   *
   * In the event no environment is specified, this test will be skipped.
   *
   * @group blt-project
   */
  public function testModulesNotEnabled() {
    $modules = $this->config['modules'][BLT_ENV]['uninstall'];
    foreach ($modules as $module) {
      $this->assertModuleNotEnabled($module, BLT_ALIAS);
    }
  }

  /**
   * Asserts that a module is not enabled.
   *
   * @param string $module
   *   The module to test.
   * @param string $alias
   *   An optional Drush alias string.
   */
  protected function assertModuleNotEnabled($module, $alias = '') {
    $enabled = $this->getModuleEnabledStatus($module, $alias);
    $this->assertFalse($enabled,
      "Expected $module to be either 'disabled,' 'not installed' or 'not found.'"
    );
  }

  /**
   * Asserts that a module is enabled.
   *
   * @param string $module
   *   The module to test.
   * @param string $alias
   *   An optional Drush alias string.
   */
  protected function assertModuleEnabled($module, $alias = '') {
    $enabled = $this->getModuleEnabledStatus($module, $alias);
    $this->assertTrue($enabled, "Expected $module to be enabled.");
  }

  /**
   * Gets a module's enabled status.
   *
   * @param string $module
   *   The module to test.
   * @param string $alias
   *   An optional Drush alias string.
   *
   * @throws \Exception
   *    If a module's status string cannot be parsed.
   *
   * @return bool
   *   TRUE if $module is enabled, FALSE if a module is either 'disabled,'
   *    'not installed' or 'not found.'
   */
  private function getModuleEnabledStatus($module, $alias = '') {
    $output = [];
    $drush_bin = $this->projectDirectory . '/vendor/bin/drush';

    // Use the project's default alias if no other alias is provided.
    $alias = !empty($alias) ? $alias : $this->config['drush']['default_alias'];

    // Get module status, it will be on the first line of output.
    exec("$drush_bin @$alias pm-list --fields=name,status --root=$this->drupalRoot | grep $module", $output);
    $status = $output[0];

    // Parse status strings, throw if parsing fails.
    if (preg_match('/enabled/i', $status)) {
      $enabled = TRUE;
    }
    elseif (preg_match('/(?:disabled|not\sinstalled|not\sfound)/i', $status)) {
      $enabled = FALSE;
    }
    else {
      throw new \Exception("Unable to parse $module's status: $status");
    }

    // Return the module's true/false enabled status.
    return $enabled;
  }

}
