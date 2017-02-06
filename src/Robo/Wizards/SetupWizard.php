<?php

namespace Acquia\Blt\Robo\Wizards;

/**
 * Class SetupWizard
 * @package Acquia\Blt\Robo\Wizards
 */
class SetupWizard extends Wizard {

  /**
   *
   */
  public function wizardInstallDrupal() {
    $this->executor->execute('echo HELLO')->printOutput(true)->run();
    if (!$this->getInspector()->isDrupalInstalled()) {
      $this->logger->warning('Drupal is not installed.');
      $confirm = $this->confirm("Do you want to install Drupal?");
      if ($confirm) {
        $bin = $this->getConfigValue('composer.bin');

        $this->executor
          ->execute("$bin/blt setup:drupal:install")->printed(true)->run();
      }
    }
  }
}
