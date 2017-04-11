<?php

namespace Acquia\Blt\Robo\Hooks;

use Acquia\Blt\Robo\Common\IO;
use Acquia\Blt\Robo\Config\ConfigAwareTrait;
use Acquia\Blt\Robo\Inspector\InspectorAwareInterface;
use Acquia\Blt\Robo\Inspector\InspectorAwareTrait;
use Consolidation\AnnotatedCommand\CommandData;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Robo\Contract\ConfigAwareInterface;

/**
 * This class provides hooks that validate configuration or state.
 *
 * These hooks should not directly provide user interaction. They should throw
 * and exception if a required condition is not met.
 *
 * Typically, each validation hook has an accompanying interact hook (which
 * runs prior to the validation hook). The interact hooks provide an
 * opportunity to the user to resolve the invalid configuration prior to an
 * exception being thrown.
 *
 * @see https://github.com/consolidation/annotated-command#validate-hook
 */
class ValidateHook implements ConfigAwareInterface, LoggerAwareInterface, InspectorAwareInterface {

  use ConfigAwareTrait;
  use LoggerAwareTrait;
  use InspectorAwareTrait;
  use IO;

  /**
   *
   */
  public function checkCommandsExist(CommandData $commandData) {
    foreach ($commands as $command) {
      if (!$this->getInspector()->commandExists($command)) {
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
    if (!$this->getInspector()->isDocrootPresent()) {
      $this->logger->error("Unable to find docroot.");

      return FALSE;
    }

    return TRUE;
  }

  /**
   * @hook validate @validateRepoRootIsPresent
   */
  public function validateRepoRootIsPresent(CommandData $commandData) {
    if (empty($this->getInspector()->isRepoRootPresent())) {
      throw new \Exception("Unable to find repository root.");
    }
  }

  /**
   * @hook validate @validateDrupalIsInstalled
   */
  public function validateDrupalIsInstalled(CommandData $commandData) {
    if (!$this->getInspector()
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
    if (!$this->getInspector()
      ->isDrupalSettingsFilePresent()
    ) {
      throw new \Exception("Could not find settings.php for this site.");
    }

    if (!$this->getInspector()->isDrupalSettingsFileValid()) {
      throw new \Exception("BLT settings are not included in settings file.");
    }
  }

  /**
   * @hook validate @validateBehatIsConfigured
   */
  public function validateBehatIsConfigured(CommandData $commandData) {
    if (!$this->getInspector()->isBehatConfigured()) {
      throw new \Exception("Behat is not properly configured properly. Please run `blt doctor` to diagnose the issue.");
    }
  }

  /**
   * @param \Consolidation\AnnotatedCommand\CommandData $commandData
   */
  public function validatePhantomJsIsConfigured(CommandData$commandData) {
    if (!$this->getInspector()->isPhantomJsConfigured()) {
      $this->logger->info("Phantom JS is not configured.");
    }
  }

  /**
   * @hook validate @validateMySqlAvailable
   */
  public function validateMySqlAvailable() {
    if (!$this->getInspector()->isMySqlAvailable()) {
      // @todo Prompt to fix.
      throw new \Exception("MySql is not available. Please run `blt doctor` to diagnose the issue.");
    }
  }

  /**
   * @hook validate validateInsideVm
   */
  public function validateInsideVm() {
    if ($this->getInspector()->isDrupalVmLocallyInitialized() && !$this->getInspector()->isVmCli()) {
      throw new \Exception("You must run this command inside Drupal VM, or else do not use Drupal VM at all. Execute `vagrant ssh`, then try again.");
    }
  }

}
