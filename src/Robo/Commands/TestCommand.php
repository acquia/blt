<?php

namespace Acquia\Blt\Robo\Commands;

use Acquia\Blt\Robo\BltTasks;

/**
 * This is project's console commands configuration for Robo task runner.
 *
 * @see http://robo.li/
 */
class TestCommand extends BltTasks
{

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
   * @wizardInstallDrupal
   *
   * @checkDocrootExists
   * @checkDrupalInstalled
   * @checkBehatIsConfigured
   */
  public function testsBehat() {
    $this->killByPort('4444');
    $this->killByName('selenium');
    $this->killByName('phantomjs');

    if ($this->getConfigValue('behat.launch-phantomjs')) {
      $this->launchPhantomJs();
    }

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
    $this->verifyPhantomJsConfig();

    $this->say("Launching PhantomJS GhostDriver.");
    $this->taskExec("{$this->bin}/phantomjs")
      ->option("webdriver", 4444)
      ->background()
      ->run();

    // Wait for http://127.0.0.1:4444/wd/hub to become available.
  }

  /**
   * @throws \Exception
   */
  protected function verifyPhantomJsConfig() {
    $result = $this->_exec("grep 'jakoch/phantomjs-installer' composer.json");
    if (!$result->wasSuccessful()) {
      $this->yell("behat.launch-phantomjs is true, but jakoch/phantomjs-installer is not required in composer.json.");
      $answer = $this->confirm("Do you want to require jakoch/phantomjs-installer via Composer?");
      if ($answer == 'y') {
        $this->_exec("composer require jakoch/phantomjs-installer --dev");
      }
      else {
        throw new \Exception("Cannot launch PhantomJS it is not installed.");
      }
    }

    $result = $this->_exec("grep installPhantomJS composer.json");
    if (!$result->wasSuccessful()) {
      $this->yell("behat.launch-phantomjs is true, but the install-phantomjs script is not defined in composer.json.");
      $answer = $this->confirm("Do you want to add an 'install-phantomjs' script to your composer.json?");
      if ($answer == 'y') {
        $this->_exec("{$this->bin}/blt-console configure:phantomjs {$this->repoRoot}");
      }
      else {
        throw new \Exception("Cannot launch PhantomJS because the install-phantomjs script is not present in composer.json. Add it, or use Selenium instead.");
      }
    }

    if (!file_exists("{$this->bin}/phantomjs")) {
      $this->_exec("composer install-phantom");
    }
  }
  /**
   * @param $port
   */
  protected function killByPort($port) {
    $this->say("Killing all processes on port $port");
    // This is allowed to fail.
    exec("lsof -ti tcp:$port | xargs kill l 2>&1");
  }

  /**
   * @param $name
   */
  protected function killByName($name) {
    $this->say("Killing all processing containing string '$name'");
    // This is allowed to fail.
    exec("pgrep $name | xargs kill l 2>&1");
  }

}
