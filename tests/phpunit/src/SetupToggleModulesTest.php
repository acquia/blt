<?php

namespace Acquia\Blt\Tests;

/**
 * Class ToggleModulesTest.
 *
 * @group orca_ignore
 */
class SetupToggleModulesTest extends BltProjectTestBase {

  /**
   * Verifies the modules for a given environment were enabled as expected.
   *
   * In the event no environment is specified, this test will be skipped.
   *
   * @throws \Exception
   */
  public function testModulesEnabled() {
    $env = $this->config->get('environment');
    $modules = (array) $this->config->get("modules.$env.enable");
    $pm_list = $this->drushJson(["pm:list", "--fields=name,status"]);
    foreach ($modules as $module) {
      $this->assertModuleEnabled($module, $pm_list);
    }
    $modules = $this->config->get("modules.$env.uninstall");
    foreach ($modules as $module) {
      $this->assertModuleNotEnabled($module, $pm_list);
    }
  }

  /**
   * Asserts that a module is not enabled.
   *
   * @param string $module
   *   The module to test.
   * @param array $pm_list
   *   List of enabled modules.
   *
   * @throws \Exception
   */
  protected function assertModuleNotEnabled($module, array $pm_list) {
    $enabled = $this->getModuleEnabledStatus($module, $pm_list);
    $this->assertFalse($enabled,
      "Expected $module to be either 'disabled,' 'not installed' or 'not found.'"
    );
  }

  /**
   * Asserts that a module is enabled.
   *
   * @param string $module
   *   The module to test.
   * @param array $pm_list
   *   List of enabled modules.
   *
   * @throws \Exception
   */
  protected function assertModuleEnabled($module, array $pm_list) {
    $enabled = $this->getModuleEnabledStatus($module, $pm_list);
    $this->assertTrue($enabled, "Expected $module to be enabled.");
  }

  /**
   * Gets a module's enabled status.
   *
   * @param string $module
   *   The module to test.
   * @param array $pm_list
   *   List of enabled modules.
   *
   * @return bool
   *   TRUE if $module is enabled, FALSE if a module is either 'disabled,'
   *    'not installed' or 'not found.'
   *
   * @throws \Exception
   *   If a module's status string cannot be parsed.
   */
  private function getModuleEnabledStatus($module, array $pm_list) {
    if (!array_key_exists($module, $pm_list)) {
      return FALSE;
    }

    $status = $pm_list[$module]['status'];
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
