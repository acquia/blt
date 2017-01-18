<?php

namespace Acquia\Blt\Robo\Hooks;

use Acquia\Blt\Robo\Common\IO;
use Acquia\Blt\Robo\LocalEnvironment\LocalEnvironmentAwareInterface;
use Acquia\Blt\Robo\LocalEnvironment\LocalEnvironmentAwareTrait;
use Consolidation\AnnotatedCommand\CommandData;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

/**
 *
 */
class ValidateHook implements LoggerAwareInterface, LocalEnvironmentAwareInterface {

  use LoggerAwareTrait;
  use LocalEnvironmentAwareTrait;
  use IO;

  /**
   *
   */
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
   * @hook validate @validateDocrootIsPresent
   */
  public function validateDocrootIsPresent(CommandData $commandData) {
    if (!$this->getLocalEnvironment()->isDocrootPresent()) {
      $this->logger->error("Unable to find docroot.");

      return FALSE;
    }

    return TRUE;
  }

  /**
   * @hook validate @validateRepoRootIsPresent
   */
  public function validateRepoRootIsPresent(CommandData $commandData) {
    if (empty($this->getLocalEnvironment()->isRepoRootPresent())) {
      throw new \Exception("Unable to find repository root.");
    }
  }

  /**
   * @hook validate @validateDrupalIsInstalled
   */
  public function validateDrupalIsInstalled(CommandData $commandData) {
    if (!$this->getLocalEnvironment()
      ->isDrupalInstalled()
    ) {

      throw new \Exception("Drupal is not installed");
    }
  }

  /**
   * Checks active settings.php file.
   *
   * @hook validate @validateSettingsFileIsValid
   */
  public function validateSettingsFileIsValid(CommandData $commandData) {
    if (!$this->getLocalEnvironment()
      ->isDrupalSettingsFilePresent()
    ) {
      throw new \Exception("Could not find settings.php for this site.");
    }

    if (!$this->getLocalEnvironment()
      ->isDrupalSettingsFileValid($this->getLocalEnvironment()
        ->getDrupalSettingsFile())
    ) {
      throw new \Exception("BLT settings are not included in settings file.");
    }
  }

  /**
   * @hook validate @validateBehatIsConfigured
   */
  public function validateBehatIsConfigured(CommandData $commandData) {
    if (!$this->getLocalEnvironment()->isBehatConfigured()) {
      $this->logger->error("Behat is not properly configured.");
      return FALSE;
    }

    return TRUE;
  }

}
