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
    $missing = FALSE;
    if (!$this->getInspector()->isDrupalLocalSettingsFilePresent()) {
      $this->logger->warning("<comment>{$this->getConfigValue('drupal.local_settings_file')}</comment> is missing.");
      $missing = TRUE;
    }
    elseif (!$this->getInspector()->isHashSaltPresent()) {
      $this->logger->warning("<comment>salt.txt</comment> is missing.");
      $missing = TRUE;
    }
    if ($missing) {
      $confirm = $this->confirm("Do you want to generate this required settings file(s)?");
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
          ->detectInteractive()
          ->run();
        $this->getInspector()->clearState();
      }
    }
  }

}
