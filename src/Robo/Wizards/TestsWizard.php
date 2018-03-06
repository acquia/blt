<?php

namespace Acquia\Blt\Robo\Wizards;

use function file_exists;
use Robo\Contract\VerbosityThresholdInterface;

/**
 * Class TestsWizard.
 *
 * @package Acquia\Blt\Robo\Wizards
 */
class TestsWizard extends Wizard {

  /**
   * Prompts user to download/install PhantomJS.
   */
  public function wizardInstallPhantomJsBinary() {
    if (!$this->getInspector()->isPhantomJsBinaryPresent()) {
      $this->logger->warning("The PhantomJS binary is not present.");
      $answer = $this->confirm("Do you want to download it?");
      if ($answer) {
        $this->say('Downloading PhantomJS...');
        $this->executor->execute("composer run-script install-phantomjs")
          ->printOutput(TRUE)
          ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
          ->run();
      }
    }
  }

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
        $bin = $this->getConfigValue('composer.bin');
        $behat_local_config_file = $this->getConfigValue('repo.root') . "/tests/behat/local.yml";
        if (file_exists($behat_local_config_file)) {
          $this->fs->remove($behat_local_config_file);
        }
        // @todo Pass all config!
        $this->executor->execute("$bin/blt tests:behat:init:config --environment=" . $this->getConfigValue('environment'))->run();
      }
    }
  }

}
