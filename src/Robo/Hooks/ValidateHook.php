<?php

namespace Acquia\Blt\Robo\Hooks;

use Acquia\Blt\Robo\Common\IO;
use Acquia\Blt\Robo\Config\ConfigAwareTrait;
use Acquia\Blt\Robo\Exceptions\BltException;
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
   * Validates that the Drupal docroot exists.
   *
   * @hook validate @validateDocrootIsPresent
   */
  public function validateDocrootIsPresent(CommandData $commandData) {
    if (!$this->getInspector()->isDocrootPresent()) {
      throw new BltException("Unable to find Drupal docroot.");
    }
  }

  /**
   * Validates that the repository root exists.
   *
   * @hook validate @validateRepoRootIsPresent
   */
  public function validateRepoRootIsPresent(CommandData $commandData) {
    if (empty($this->getInspector()->isRepoRootPresent())) {
      throw new BltException("Unable to find repository root.");
    }
  }

  /**
   * Validates that Drupal is installed.
   *
   * @hook validate @validateDrupalIsInstalled
   */
  public function validateDrupalIsInstalled(CommandData $commandData) {
    if (!$this->getInspector()
      ->isDrupalInstalled()
    ) {

      throw new BltException("Drupal is not installed");
    }
  }

  /**
   * Checks active settings.php file.
   *
   * @hook validate @validateSettingsFileIsValid
   */
  public function validateSettingsFileIsValid(CommandData $commandData) {
    if (!$this->getInspector()->isDrupalSettingsFilePresent()) {
      throw new BltException("Could not find settings.php for this site.");
    }

    if (!$this->getInspector()->isDrupalSettingsFileValid()) {
      throw new BltException("BLT settings are not included in settings file.");
    }
  }

  /**
   * Validates that Behat is properly configured on the local machine.
   *
   * @hook validate @validateBehatIsConfigured
   */
  public function validateBehatIsConfigured(CommandData $commandData) {
    if (!$this->getInspector()->isBehatConfigured()) {
      throw new BltException("Behat is not configured properly. Please run `blt doctor` to diagnose the issue.");
    }
  }

  /**
   * Validates that MySQL is available.
   *
   * @hook validate @validateMySqlAvailable
   */
  public function validateMySqlAvailable() {
    if (!$this->getInspector()->isMySqlAvailable()) {
      throw new BltException("MySql is not available. Please run `blt doctor` to diagnose the issue.");
    }
  }

  /**
   * Validates that required settings files exist.
   *
   * @hook validate @validateSettingsFilesPresent
   */
  public function validateSettingsFilesPresent() {
    if (!$this->getInspector()->isHashSaltPresent()) {
      throw new BltException("salt.txt is not present. Please run `blt blt:init:settings` to generate it.");
    }
    if (!$this->getInspector()->isDrupalLocalSettingsFilePresent()) {
      throw new BltException("Could not find settings.php for this site.");
    }
    // @todo Look for local.drush.yml.
  }

  /**
   * Validates that current PHP process is being executed inside of the VM.
   *
   * @hook validate validateVmConfig
   */
  public function validateVmConfig() {
    if ($this->getInspector()->isDrupalVmLocallyInitialized() && $this->getInspector()->isDrupalVmBooted() && !$this->getInspector()->isDrupalVmConfigValid()) {
      throw new BltException("Drupal VM configuration is invalid.");
    }
  }

}
