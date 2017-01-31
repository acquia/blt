<?php

namespace Acquia\Blt\Robo\Wizards;

/**
 * Class TestsWizard
 * @package Acquia\Blt\Robo\Wizards
 */
class TestsWizard extends Wizard {

  /**
   * @throws \Exception
   */
  public function wizardRequirePhantomJs() {
    if (!$this->getInspector()->isPhantomJsRequired()) {
      $this->logger->warning("PhantomJS is not required in composer.json");
      $answer = $this->confirm("Do you want to require jakoch/phantomjs-installer as a dev dependency?");
      if ($answer) {
        $this->executor->execute("composer require jakoch/phantomjs-installer --dev");
      }
      else {
        throw new \Exception("Cannot launch PhantomJS it is not installed.");
      }
    }
  }

  /**
   * @throws \Exception
   */
  public function wizardConfigurePhantomJsScript() {
    if (!$this->getInspector()->isPhantomJsScriptConfigured()) {
      $this->logger->warning("The install-phantomjs script is not defined in composer.json.");
      $answer = $this->confirm("Do you want to add an 'install-phantomjs' script to your composer.json?");
      if ($answer) {
        $this->executor->execute("{$this->getConfigValue('composer.bin')}/blt-console configure:phantomjs {$this->getConfigValue('repo.root')}");
      }
      else {
        throw new \Exception("Cannot launch PhantomJS because the install-phantomjs script is not present in composer.json. Add it, or use Selenium instead.");
      }
    }
  }

  /**
   *
   */
  public function wizardInstallPhantomJsBinary() {
    if (!$this->getInspector()->isPhantomJsBinaryPresent()) {
      $this->logger->warning("The PhantomJS binary is not present.");
      $answer = $this->confirm("Do you want to install it?");
      if ($answer) {
        $this->executor->execute("composer install-phantom");
      }
    }
  }

  /**
   *
   */
  public function wizardConfigureBehat() {
    if (!$this->getInspector()->isBehatConfigured()) {
      $this->logger->warning('Behat is not configured properly.');
      $confirm = $this->confirm("Do you want re-generate local Behat config at tests/behat/local.yml?");
      if ($confirm) {
        $bin = $this->getConfigValue('composer.bin');
        $behat_local_config_file = $this->getConfigValue('repo.root') . "/tests/behat/local.yml";
        if (file_exists($behat_local_config_file)) {
          $this->fs->remove($behat_local_config_file);
        }
        $this->executor->execute("$bin/blt setup:behat");
      }
    }
  }

}
