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
 * These hooks should not directly provide user interaction. They should attempt
 * to fail gracefully or throw an exception if a required condition is not met.
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
   * Validates that Git user is configured.
   *
   * @hook validate @validateGitConfig
   */
  public function validateGitConfig() {
    if (!$this->getInspector()->isGitMinimumVersionSatisfied('2.0')) {
      throw new BltException("Your system does not meet BLT's requirements. Please update git to 2.0 or newer.");
    }
    if (!$this->getInspector()->isGitUserSet()) {
      if (!$this->getConfigValue('git.user.name') || !$this->getConfigValue('git.user.email')) {
        $this->logger->warning("Git user name or email is not configured. BLT will attempt to set a dummy user and email address for this commit.");
        $this->config->set('git.user.name', 'BLT dummy user');
        $this->config->set('git.user.email', 'no-reply@example.com');
      }
    }
  }

}
