<?php

namespace Acquia\Blt\Robo\Commands\Setup;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Exceptions\BltException;
use Symfony\Component\Console\Input\InputOption;

/**
 * Defines commands in the "setup:toggle-modules" namespace.
 */
class ToggleModulesCommand extends BltTasks {

  /**
   * Enables and uninstalls specified modules.
   *
   * You may define the environment for which modules should be toggled by
   * passing the --environment=[value] option to this command setting
   * $_ENV['environment'] via the CLI, or defining environment in one of your
   * BLT configuration files.
   *
   * @command setup:toggle-modules
   *
   * @option environment The environment key for which modules should be
   *   toggled. This should correspond with a modules.[environment].* key in
   *   your configuration.
   *
   * @executeInDrupalVm
   */
  public function toggleModules($options = [
    'environment' => InputOption::VALUE_REQUIRED,
  ]) {
    if ($options['environment']) {
      $environment = $options['environment'];
    }
    elseif ($this->getConfig()->has('environment')) {
      $environment = $this->getConfigValue('environment');
    }
    elseif (!empty($_ENV['environment'])) {
      $environment = $_ENV['environment'];
    }

    if (isset($environment)) {
      // Enable modules.
      $enable_key = "modules.$environment.enable";
      $this->doToggleModules('pm-enable', $enable_key);

      // Uninstall modules.
      $disable_key = "modules.$environment.uninstall";
      $this->doToggleModules('pm-uninstall', $disable_key);
    }
    else {
      $this->say("Environment is unset. Skipping setup:toggle-modules...");
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
   * @throws \Acquia\Blt\Robo\Exceptions\BltException
   */
  protected function doToggleModules($command, $config_key) {
    if ($this->getConfig()->has($config_key)) {
      $modules = (array) $this->getConfigValue($config_key);
      $modules_list = implode(' ', $modules);
      $result = $this->taskDrush()
        ->drush("$command $modules_list")
        ->assume(TRUE)
        ->run();
      $exit_code = $result->getExitCode();
    }
    else {
      $exit_code = 0;
      $this->logger->info("$config_key is not set.");
    }

    if ($exit_code) {
      throw new BltException("Could not toggle modules listed in $config_key.");
    }
  }

}
