<?php

namespace Acquia\Blt\Robo\Wizards;

use function file_exists;

/**
 * Class TestsWizard.
 *
 * @package Acquia\Blt\Robo\Wizards
 */
class TestsWizard extends Wizard {

  /**
   * Prompts user to generate valid Behat configuration file.
   */
  public function wizardConfigureBehat() {
    $behat_local_config_file = $this->getConfigValue('repo.root') . '/tests/behat/local.yml';
    if (!file_exists($behat_local_config_file) || !$this->getInspector()->isBehatConfigured()) {
      $this->logger->warning('Behat is not configured properly.');
      $this->say("BLT can (re)generate tests/behat/local.yml using tests/behat/example.local.yml.");
      $confirm = $this->confirm("Do you want (re)generate local Behat config in <comment>tests/behat/local.yml</comment>?", TRUE);
      if ($confirm) {
        $this->getConfigValue('composer.bin');
        $behat_local_config_file = $this->getConfigValue('repo.root') . "/tests/behat/local.yml";
        if (file_exists($behat_local_config_file)) {
          $this->fs->remove($behat_local_config_file);
        }
        $this->invokeCommand('tests:behat:init:config');
      }
    }
  }

}
