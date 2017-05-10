<?php

namespace Acquia\Blt\Robo\Wizards;

/**
 * Class SetupWizard.
 *
 * @package Acquia\Blt\Robo\Wizards
 */
class SetupWizard extends Wizard {

  /**
   * Wizard for generating setup files.
   *
   * Executes blt setup:settings command.
   */
  public function wizardGenerateSettingsFiles() {
    if (!$this->getInspector()->isDrupalLocalSettingsFilePresent()) {
      $this->logger->warning('The drupal local.settings.php file is missing.');
      $confirm = $this->confirm("Do you want to generate required settings files?");
      if ($confirm) {
        $bin = $this->getConfigValue('composer.bin');
        $this->executor
          ->execute("$bin/blt setup:settings")->printOutput(TRUE)->run();
      }
    }
  }

  /**
   * Wizard for installing Drupal.
   *
   * Executes blt setup:drupal:install.
   */
  public function wizardInstallDrupal() {
    if (!$this->getInspector()->isMySqlAvailable()) {
      return FALSE;
    }
    if (!$this->getInspector()->isDrupalInstalled()) {
      $this->logger->warning('Drupal is not installed.');
      $confirm = $this->confirm("Do you want to install Drupal?");
      if ($confirm) {
        $bin = $this->getConfigValue('composer.bin');
        $this->executor
          ->execute("$bin/blt setup")
          ->interactive(TRUE)
          ->run();
      }
    }
  }

}
