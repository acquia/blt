<?php

namespace Acquia\Blt\Robo\Wizards;

use Robo\Contract\VerbosityThresholdInterface;

/**
 * Class TestsWizard
 * @package Acquia\Blt\Robo\Wizards
 */
class TestsWizard extends Wizard {

  /**
   * Prompts user to download/install PhantomJS.
   */
  public function wizardInstallPhantomJsBinary() {
    if (!$this->getInspector()->isPhantomJsBinaryPresent()) {
      $this->logger->warning("The PhantomJS binary is not present.");
      $answer = $this->confirm("Do you want to install it?");
      if ($answer) {
        $this->executor->execute("composer run-script install-phantomjs")
          ->printOutput(true)
          ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
          ->run();
      }
    }
  }

  /**
   * Prompts user to generate valid Behat configuration file.
   */
  public function wizardConfigureBehat() {
    if (!$this->getInspector()->isBehatConfigured()) {
      $this->logger->warning('Behat is not configured properly.');
      $confirm = $this->confirm("Do you want re-generate local Behat config at tests/behat/local.yml?", true);
      if ($confirm) {
        $bin = $this->getConfigValue('composer.bin');
        $behat_local_config_file = $this->getConfigValue('repo.root') . "/tests/behat/local.yml";
        if (file_exists($behat_local_config_file)) {
          $this->fs->remove($behat_local_config_file);
        }
        $this->executor->execute("$bin/blt setup:behat")->run();
      }
    }
  }

}
