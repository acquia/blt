<?php

namespace Acquia\Blt\Robo\Commands\Setup;

use Acquia\Blt\Robo\BltTasks;
use Robo\Contract\VerbosityThresholdInterface;
use Symfony\Component\Finder\Finder;

/**
 * Defines commands in the "setup:toggle-modules" namespace.
 */
class ToggleModulesCommand extends BltTasks {

  /**
   * Enables and uninstalls specified modules.
   *
   * @command setup:toggle-modules
   */
  public function toggleModules() {
    if (!empty($_ENV['environment'])) {
      // Enable modules.
      $enable_key = "modules.{$_ENV['environment']}.enable";
      $exit_code = $this->doToggleModules('pm-enable', $enable_key);

      // Uninstall modules.
      $disable_key = "modules.{$_ENV['environment']}.uninstall";
      $exit_code = $this->doToggleModules('pm-uninstall', $disable_key);

      return $exit_code;
    }
  }

  /**
   * Enables or uninstalls an array of modules.
   *
   * @param string $command
   *   The drush command to execute. E.g., pm-enable or pm-uninstall.
   * @param string $config_key
   *   The config key containing the array of modules.
   *
   * @return int
   *   The exit code of the command.
   */
  protected function doToggleModules($command, $config_key) {
    if ($this->getConfig()->has($config_key)) {
      $modules = $this->getConfigValue($config_key);
      $modules_list = implode(' ', $modules);
      $result = $this->taskDrush()
        ->drush("$command $modules_list --skip")
        ->run();
      $exit_code = $result->getExitCode();
    }
    else {
      $exit_code = 0;
      $this->logger->info("$config_key is not set.");
    }

    return $exit_code;
  }
}
