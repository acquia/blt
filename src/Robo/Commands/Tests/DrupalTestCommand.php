<?php

namespace Acquia\Blt\Robo\Commands\Tests;

use Acquia\Blt\Robo\Exceptions\BltException;
use Robo\Contract\VerbosityThresholdInterface;

/**
 * Defines commands in the "tests" namespace.
 */
class DrupalTestCommand extends TestsCommandBase {

  const APACHE_RUN_GROUP = 'APACHE_RUN_GROUP';
  const APACHE_RUN_USER = 'APACHE_RUN_USER';
  const BROWSERTEST_OUTPUT_DIRECTORY = 'BROWSERTEST_OUTPUT_DIRECTORY';
  const DRUPAL_TEST_BASE_URL = 'DRUPAL_TEST_BASE_URL';
  const DRUPAL_TEST_WEBDRIVER_CHROME_ARGS = 'DRUPAL_TEST_WEBDRIVER_CHROME_ARGS';
  const MINK_DRIVER_ARGS = 'MINK_DRIVER_ARGS';
  const MINK_DRIVER_ARGS_WEBDRIVER = 'MINK_DRIVER_ARGS_WEBDRIVER';
  const MINK_DRIVER_CLASS = 'MINK_DRIVER_CLASS';
  const SIMPLETEST_BASE_URL = 'SIMPLETEST_BASE_URL';
  const SIMPLETEST_DB = 'SIMPLETEST_DB';
  const SYMFONY_DEPRECATIONS_HELPER = 'SYMFONY_DEPRECATIONS_HELPER';

  /**
   * Directory to store output printer files.
   *
   * @var string
   */
  protected $browsertestOutputDirectory;

  /**
   * @var string
   */
  protected $apacheRunUser;

  /**
   * @var bool
   */
  protected $sudoRunTests;

  /**
   * Environment variables to set for Drupal tests.
   *
   * @var array
   */
  protected $testingEnv;

  /**
   * Environment variables to exported before Drupal tests.
   *
   * @var string
   */
  protected $testingEnvString;

  /**
   * The method for running Drupal test; either phpunit or run-tests.sh.
   *
   * @var string
   */
  protected $drupalTestRunner;

  /**
   * This hook will fire for all commands in this command file.
   *
   * @hook init
   */
  public function initialize() {
    parent::initialize();
    $this->drupalTestRunner = $this->getConfigValue('tests.drupal.test-runner');
  }

  /**
   * Setup and run tests.
   */
  public function run() {
    try {
      $this->setTestingConfig();
      $this->getTestingEnvString();
      $this->createLogs();
      $this->launchWebDriver();
      $this->executeTests();
      $this->killWebDriver();
    }
    catch (\Exception $e) {
      // Kill web driver server to prevent Pipelines from hanging after fail.
      $this->killWebDriver();
      throw $e;
    }
  }

  /**
   * Executes tests found in either tests.phpunit or tests.drupal-tests.
   *
   * @command tests:drupal:run
   * @aliases tdr
   * @description Executes tests with Drupal core's testing framework. Launches chromedriver prior to execution.
   *
   * @interactGenerateSettingsFiles
   * @interactInstallDrupal
   * @validateDrupalIsInstalled
   * @validateVmConfig
   * @launchWebServer
   * @executeInVm
   *
   * @throws \Exception
   *   Throws an exception if any Drupal test fails.
   */
  public function executeTests() {
    $this->logger->notice("Ensure that you have installed Drupal Core's dev dependencies (such as via https://github.com/webflo/drupal-core-require-dev) prior to running this command.");
    if ($this->drupalTestRunner == 'phpunit') {
      $this->invokeCommand('tests:drupal:phpunit:run');
    }
    elseif ($this->drupalTestRunner == 'run-tests-script') {
      $this->invokeCommand('tests:drupal:run-tests:run');
    }
    elseif ($this->drupalTestRunner == 'nightwatch') {
      if (!$this->validateCommand('yarn')) {
        return;
      }
      $this->invokeCommand('tests:drupal:nightwatch:run');
    }
    else {
      throw new BltException("You must have tests.drupal.test-runner set to either phpunit or run-tests-script.");
    }
  }

  /**
   * Validate that a command exists.
   */
  protected function validateCommand(string $command) {
    // phpcs:ignore
    if (empty(shell_exec("which $command"))) {
      $this->logger->warning("Cannot find $command. The test runner you have selected, {$this->drupalTestRunner}, requires $command.");
      return FALSE;
    }
    return TRUE;
  }

  /**
   * Get environment variables string used for running Drupal tests.
   */
  protected function setTestingConfig() {
    $this->browsertestOutputDirectory = $this->reportsDir . '/' . $this->getConfigValue('tests.drupal.browsertest-output-directory');
    $this->apacheRunUser = $this->getConfigValue('tests.drupal.apache-run-user');
    $this->sudoRunTests = $this->getConfigValue('tests.drupal.sudo-run-tests');
    $this->testingEnv = [
      self::APACHE_RUN_GROUP => $this->sudoRunTests ? $this->getConfigValue('tests.drupal.apache-run-user') : NULL,
      self::APACHE_RUN_USER => $this->sudoRunTests ? $this->apacheRunUser : NULL,
      self::BROWSERTEST_OUTPUT_DIRECTORY => $this->browsertestOutputDirectory,
      self::DRUPAL_TEST_BASE_URL => $this->getConfigValue('tests.drupal.simpletest-base-url'),
      self::DRUPAL_TEST_WEBDRIVER_CHROME_ARGS => $this->getConfigValue('tests.drupal.chrome-args'),
      self::MINK_DRIVER_ARGS => $this->getConfigValue('tests.drupal.mink-driver-args'),
      self::MINK_DRIVER_ARGS_WEBDRIVER => $this->getConfigValue('tests.drupal.mink-driver-args-webdriver'),
      self::MINK_DRIVER_CLASS => $this->getConfigValue('tests.drupal.mink-driver-class'),
      self::SIMPLETEST_BASE_URL => $this->getConfigValue('tests.drupal.simpletest-base-url'),
      self::SIMPLETEST_DB => $this->getConfigValue('tests.drupal.simpletest-db'),
      self::SYMFONY_DEPRECATIONS_HELPER => $this->getConfigValue('tests.drupal.symfony-deprecations-helper'),
    ];
  }

  /**
   * Get environment variables string used for running Drupal tests.
   */
  protected function getTestingEnvString() {
    $testingEnv = array_filter($this->testingEnv);
    array_walk($testingEnv, function (&$value, $key) {
      $value = "{$key}='{$value}'";
    });
    $this->testingEnvString = implode(' ', $testingEnv);
  }

  /**
   * Launch the appropriate web driver based on configuration.
   */
  protected function launchWebDriver() {
    if ($this->getConfigValue('tests.drupal.web-driver') == 'chromedriver') {
      $this->launchSelenium();
      $this->launchChromeDriver();
    }
    elseif ($this->getConfigValue('tests.drupal.web-driver') == 'chrome') {
      $this->launchChrome();
    }
  }

  /**
   * Kills the appropriate web driver based on configuration.
   */
  protected function killWebDriver() {
    if ($this->getConfigValue('tests.drupal.web-driver') == 'chromedriver') {
      $this->killSelenium();
      $this->killChromeDriver();
    }
    elseif ($this->getConfigValue('tests.drupal.web-driver') == 'chrome') {
      $this->killChrome();
    }
  }

  /**
   * Creates empty log directory and log file for PHPUnit tests.
   */
  protected function createLogs() {
    $this->taskFilesystemStack()
      ->mkdir($this->reportsDir)
      ->mkdir($this->browsertestOutputDirectory)
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
      ->run();
  }

}
