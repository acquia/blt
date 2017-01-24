<?php

namespace Acquia\Blt\Robo\Commands;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Wizards\TestsWizard;
use Drupal\Core\Database\Log;
use GuzzleHttp\Client;
use Psr\Log\LogLevel;
use Wikimedia\WaitConditionLoop;

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
   * @interactLaunchPhpWebServer
   * @interactInstallDrupal
   * @interactConfigureBehat
   *
   * @validateDrupalIsInstalled
   * @validateBehatIsConfigured
   */
  public function testsBehat() {
    $this->logConfig($this->getConfigValue('behat'), 'behat');
    $this->logConfig($this->getInspector()->getLocalBehatConfig()->toArray());

    // Kill these processes, regardless of what config is enabled.
    $this->getContainer()->get('executor')->killProcessByPort('4444');
    $this->getContainer()->get('executor')->killProcessByName('selenium');
    $this->getContainer()->get('executor')->killProcessByName('phantomjs');

    if ($this->getConfigValue('behat.launch-phantomjs')) {
      $this->launchPhantomJs();
    }
    elseif ($this->getConfigValue('behat.launch-selenium')) {
      $this->launchSelenium();
    }

    $this->taskFilesystemStack()
      ->mkdir($this->getConfigValue('reports.localDir'))
      //->setLogLevel(LogLevel::DEBUG)
      ->run();

    foreach ($this->getConfigValue('behat.paths') as $behat_path) {
      // Output errors.
      // @todo break if fails.
      // @todo replace base_url in behat config when internal server is being used.
      $command = "{$this->getConfigValue('composer.bin')}/behat --strict $behat_path -c {$this->getConfigValue('behat.config')} -p {$this->getConfigValue('behat.profile')}";
      $this->taskExec($command)
        ->interactive(TRUE)
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
    $reports_dir = $this->getConfigValue('reports.localDir') . '/phpunit';
    $report_file = $reports_dir . '/results.xml';
    $this->_mkdir($reports_dir);
    $this->_touch($report_file);
    $tests_dir = $this->getConfigValue('repo.root') . '/tests/phpunit';

    $this->taskPHPUnit()
      ->dir($tests_dir)
      ->xml($report_file)
      ->arg('.')
      ->printOutput(true)
      ->run();
  }

  /**
   * Check local Drupal installation for security updates.
   *
   * @command tests:security-updates
   * @description Check local Drupal installation for security updates.
   */
  public function testsSecurityUpdates() {
    $passed = $this->taskExec("! drush -n ups --check-disabled --security-only 2>/dev/null | grep 'SECURITY UPDATE'")
      ->run()->wasSuccessful();
    if ($passed) {
      $this->logger->error("One or more of your dependency has an outstanding security update. Please apply update(s) immediately.");
    }
  }

  /**
   *
   */
  protected function launchSelenium() {
    $this->say("Launching Selenium standalone server.");
    $log_file = $this->getConfigValue('reports.localDir') . "/selenium2.log";
    // @todo set log level to debug.
    $this->_touch($log_file);
    $this->taskExec("{$this->getConfigValue('composer.bin')}/selenium-server-standalone -port 4444 -log $log_file  > /dev/null 2>&1")
      ->background(true)
      ->printOutput(true)
      //->setLogLevel(LogLevel::DEBUG)
      ->dir($this->getConfigValue('repo.root'))
      ->run();
    $this->logger->info("Selenium2 logs are being written to $log_file");
    $url = "http://127.0.0.1:4444/wd/hub";
    $this->say("Waiting for Selenium standalone server ($url) to become available.");
    $this->getContainer()->get('executor')->waitForUrlAvailable($url);
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
      //->setLogLevel(LogLevel::INFO)
      ->background()
      ->run();
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

}
