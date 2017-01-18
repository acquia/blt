<?php

namespace Acquia\Blt\Robo\Commands;

use Acquia\Blt\Robo\BltTasks;

/**
 * This is project's console commands configuration for Robo task runner.
 *
 * @see http://robo.li/
 */
class TestCommand extends BltTasks {

  /**
   * Runs all tests, including Behat, PHPUnit, and Security Update check.
   *
   * @command tests:all
   *
   * @calls tests:behat, tests:phpunit
   */
  public function tests() {
  }

  /**
   * @command tests:behat
   *
   * @interactInstallDrupal
   * @interactConfigureBehat
   *
   * @validateDrupalIsInstalled
   * @validateBehatIsConfigured
   */
  public function testsBehat() {
    $this->killByPort('4444');
    $this->killByName('selenium');
    $this->killByName('phantomjs');

    if ($this->getConfigValue('behat.launch-phantomjs')) {
      $this->launchPhantomJs();
    }

    // @todo Figure out how to make this less noisy. Ugly escaped slashes in filepath output.
    $this->_mkdir("{$this->getConfigValue('repo.root')}/{$this->getConfigValue('reports.localDir')}");

    foreach ($this->getConfigValue('behat.paths') as $behat_path) {
      // Output errors.
      // @todo break if fails.
      $command = "{$this->getConfigValue('composer.bin')}/behat $behat_path -c {$this->getConfigValue('behat.config')} -p {$this->getConfigValue('behat.profile')}";
      $this->taskExec($command)
        ->interactive()
        ->run()
        ->stopOnFail();
    }
  }

  /**
   * @command tests:phpunit
   */
  public function testsPhpUnit() {

  }

  /**
   *
   */
  protected function launchPhantomJs() {
    if (!$this->getLocalEnvironment()->isPhantomJsConfigured()) {
      $this->setupPhantomJs();
    }

    $this->say("Launching PhantomJS GhostDriver.");
    $this->taskExec("{$this->getConfigValue('composer.bin')}/phantomjs")
      ->option("webdriver", 4444)
      ->background()
      ->run();

    // Wait for http://127.0.0.1:4444/wd/hub to become available.
  }

  /**
   * @command setup:phantomjs
   *
   * @validatePhantomJsIsConfigured
   */
  public function setupPhantomJs() {
      $this->interactRequirePhantomJs();
      $this->interactConfigurePhantomJsScript();
      $this->interactInstallPhantomJsBinary();
  }

  protected function interactRequirePhantomJs() {
    if (!$this->getLocalEnvironment()->isPhantomJsRequired()) {
      $this->logger->warning("PhantomJS is not required in composer.json");
      $answer = $this->confirm("Do you want to require jakoch/phantomjs-installer as a dev dependency?");
      if ($answer) {
        $this->_exec("composer require jakoch/phantomjs-installer --dev");
      }
      else {
        throw new \Exception("Cannot launch PhantomJS it is not installed.");
      }
    }
  }

  protected function interactConfigurePhantomJsScript() {
    if (!$this->getLocalEnvironment()->isPhantomJsScriptConfigured()) {
      $this->logger->warning("The install-phantomjs script is not defined in composer.json.");
      $answer = $this->confirm("Do you want to add an 'install-phantomjs' script to your composer.json?");
      if ($answer) {
        $this->_exec("{$this->getConfigValue('composer.bin')}/blt-console configure:phantomjs {$this->getConfigValue('repo.root')}");
      }
      else {
        throw new \Exception("Cannot launch PhantomJS because the install-phantomjs script is not present in composer.json. Add it, or use Selenium instead.");
      }
    }
  }

  protected function interactInstallPhantomJsBinary() {
    if (!$this->getLocalEnvironment()->isPhantomJsBinaryPresent()) {
      $this->logger->warning("The PhantomJS binary is not present.");
      $answer = $this->confirm("Do you want to install it?");
      if ($answer) {
        $this->_exec("composer install-phantom");
      }
    }
  }

  /**
   * @param $port
   */
  protected function killByPort($port) {
    $this->logger->info("Killing all processes on port $port");
    // This is allowed to fail.
    // @todo Replace with standardized call to Symfony Process.
    exec("lsof -ti tcp:$port | xargs kill l 2>&1");
  }

  /**
   * @param $name
   */
  protected function killByName($name) {
    $this->logger->info("Killing all processing containing string '$name'");
    // This is allowed to fail.
    // @todo Replace with standardized call to Symfony Process.
    exec("pgrep $name | xargs kill l 2>&1");
  }

}
