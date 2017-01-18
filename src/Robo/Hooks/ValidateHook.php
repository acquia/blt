<?php

namespace Acquia\Blt\Robo\Hooks;

use Acquia\Blt\Robo\Common\IO;
use Acquia\Blt\Robo\LocalEnvironment\LocalEnvironmentInterface;
use Acquia\Blt\Robo\LocalEnvironment\LocalEnvironmentTrait;
use Consolidation\AnnotatedCommand\CommandData;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class ValidateHook implements LoggerAwareInterface, LocalEnvironmentInterface {

  use LoggerAwareTrait;
  use LocalEnvironmentTrait;
  use IO;

  public function checkCommandsExist(CommandData $commandData) {
    foreach ($commands as $command) {
      if (!$this->getLocalEnvironment()->commandExists($command)) {
        $this->yell("Unable to find '$command' command!");
        return FALSE;
      }
    }

    return TRUE;
  }

  /**
   * @hook validate @checkDocrootExists
   */
  public function checkDocrootExists(CommandData $commandData) {
    if (!$this->getLocalEnvironment()->docrootExists()) {
      $this->logger->error("Unable to find docroot.");

      return FALSE;
    }

    return TRUE;
  }

  /**
   * @hook validate @checkRepoRootExists
   */
  public function checkRepoRootExists(CommandData $commandData) {
    if (empty($this->getLocalEnvironment()->getRepoRoot())) {
      throw new \Exception("Unable to find repository root.");
    }
  }

  /**
   * @hook validate @checkDrupalInstalled
   */
  public function checkDrupalInstalled(CommandData $commandData) {
    if (!$this->getLocalEnvironment()
      ->drupalIsInstalled($this->getLocalEnvironment()->getDocroot())
    ) {

      throw new \Exception("Drupal is not installed");
    }
  }

  /**
   * Checks active settings.php file.
   *
   * @hook validate @checkSettingsFile
   */
  public function checkSettingsFile(CommandData $commandData) {
    if (!$this->getLocalEnvironment()
      ->drupalSettingsFileExists($this->getLocalEnvironment()
        ->getDrupalSettingsFile())
    ) {
      throw new \Exception("Could not find settings.php for this site.");
    }

    if (!$this->getLocalEnvironment()
      ->drupalSettingsFileIsValid($this->getLocalEnvironment()
        ->getDrupalSettingsFile())
    ) {
      throw new \Exception("BLT settings are not included in settings file.");
    }
  }

  /**
   * @hook validate @checkBehatIsConfigured
   */
  public function checkBehatIsConfigured(CommandData $commandData) {
    if (!$this->getLocalEnvironment()->behatIsConfigured()) {
      $this->logger->error("Behat is not properly configured.");
      return FALSE;
    }

    return TRUE;
  }

}
