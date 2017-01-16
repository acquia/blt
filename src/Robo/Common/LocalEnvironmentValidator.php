<?php

namespace Acquia\Blt\Robo\Common;

/**
 * Class LocalEnvironmentValidator
 * @package Acquia\Blt\Robo\Common
 */
class LocalEnvironmentValidator {

  use IO;

  /** @var LocalEnvironment */
  protected $localEnvironment;

  /**
   * LocalEnvironmentRequirer constructor.
   *
   * @param \Acquia\Blt\Robo\Common\LocalEnvironment $local_environment
   */
  public function __construct(LocalEnvironment $local_environment) {
    $this->localEnvironment = $local_environment;
  }

  /**
   * @param $methods
   *
   * @return bool
   */
  public function performLocalEnvironmentChecks($methods) {
    foreach ($methods as $method) {
      if (!$this->$method()) {
        return FALSE;
      }
    }
  }

  /**
   * Check if an array of commands exists on the system.
   *
   * @param $commands array An array of command binaries.
   *
   * @return bool
   *   TRUE if all commands exist, otherwise FALSE.
   */
  protected function checkCommandsExist($commands) {
    foreach ($commands as $command) {
      if (!$this->localEnvironment->commandExists($command)) {
        $this->yell("Unable to find '$command' command!");
        return FALSE;
      }
    }

    return TRUE;
  }

  /**
   * @return bool
   *   FALSE if repo root cannot be found.
   */
  protected function checkDocrootExists() {
    if (empty($this->docroot) || !file_exists($this->docroot)) {
      $this->error("Unable to find docroot.");

      return FALSE;
    }

    return TRUE;
  }

  /**
   * @return bool
   *   FALSE if repo root cannot be found.
   */
  protected function checkRepoRootExists() {
    if (empty($this->localEnvironment->getRepoRoot())) {
      $this->error("Unable to find repository root.");
      $this->say("This command must be run from a BLT-generated project directory.");

      return FALSE;
    }

    return TRUE;
  }

  /**
   * @return bool
   */
  protected function checkDrupalInstalled() {
    if ($this->localEnvironment->drupalIsInstalled($this->localEnvironment->getDocroot())) {
      return TRUE;
    }

    $this->error("Drupal is not installed");
    return FALSE;
  }

  /**
   * Checks active settings.php file.
   */
  protected function checkSettingsFile() {
    if (!$this->localEnvironment->drupalSettingsFileExists($this->localEnvironment->getDrupalSettingsFile())) {
      $this->error("Could not find settings.php for this site.");
      return FALSE;
    }

    if (!$this->localEnvironment->drupalSettingsFileIsValid($this->localEnvironment->getDrupalSettingsFile())) {
      $this->error("BLT settings are not included in settings file.");
      return FALSE;
    }

    return TRUE;
  }
}
