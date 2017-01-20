<?php

namespace Acquia\Blt\Robo\Commands;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Wizards\TestsWizard;

/**
 * Defines commands in the "tests" namespace.
 */
class TestsCommand extends BltTasks {

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
   * Executes all behat tests.
   *
   * @command tests:behat
   * @description Executes all behat tests. This optionally launch PhantomJS or Selenium prior to execution.
   * @usage
   *   Executes all configured tests.
   * @usage -Dbehat.paths=${PWD}/tests/behat/features/Examples.feature
   *   Executes scenarios in the Examples.feature file.
   * @usage -Dbehat.paths=${PWD}/tests/behat/features/Examples.feature:4
   *   Executes only the scenario on line 4 of Examples.feature.
   *
   * @interactInstallDrupal
   * @interactConfigureBehat
   *
   * @validateDrupalIsInstalled
   * @validateBehatIsConfigured
   */
  public function testsBehat() {
    $this->logConfig($this->getConfigValue('behat'), 'behat');
    $this->killByPort('4444');
    $this->killByName('selenium');
    $this->killByName('phantomjs');

    if ($this->getConfigValue('behat.launch-phantomjs')) {
      $this->launchPhantomJs();
    }

    // @todo Figure out how to make this less noisy. Ugly escaped slashes in file path output.
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
   * Executes all PHPUnit tests.
   *
   * @command tests:phpunit
   * @description Executes all PHPUnit tests.
   */
  public function testsPhpUnit() {

  }

  /**
   *
   */
  protected function launchPhantomJs() {
    if (!$this->getInspector()->isPhantomJsConfigured()) {
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
    /** @var TestsWizard $tests_wizard */
    $tests_wizard = $this->getContainer()->get(TestsWizard::class);
    $tests_wizard->wizardRequirePhantomJs();
    $tests_wizard->wizardConfigurePhantomJsScript();
    $tests_wizard->wizardInstallPhantomJsBinary();
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
