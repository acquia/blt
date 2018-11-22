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
  const MINK_DRIVER_ARGS = 'MINK_DRIVER_ARGS';
  const MINK_DRIVER_ARGS_PHANTOMJS = 'MINK_DRIVER_ARGS_PHANTOMJS';
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
   * Environment varialbes to set for Drupal tests.
   *
   * @var array
   */
  protected $testingEnv;

  /**
   * Environment varialbes to exported before Drupal tests.
   *
   * @var string
   */
  protected $testingEnvString;

  /**
   * Directory in which test logs and reports are generated.
   *
   * @var string
   */
  protected $reportsDir;

  /**
   * The filename for PHPUnit report.
   *
   * @var string
   */
  protected $reportFile;

  /**
   * The method for running Drupal test; either phpunit or run-tests.sh.
   *
   * @var string
   */
  protected $drupalTestRunner;

  /**
   * @var string
   */
  protected $chromeDriverPort;

  /**
   * @var string
   */
  protected $chromeDriverArgs;

  /**
   * This hook will fire for all commands in this command file.
   *
   * @hook init
   */
  public function initialize() {
    // Log config for debugging purposes.
    $this->logConfig($this->getConfigValue('tests'), 'tests');

    $this->reportsDir = $this->getConfigValue('reports.localDir') . '/phpunit';
    $this->reportFile = $this->reportsDir . '/results.xml';
    $this->drupalTestRunner = $this->getConfigValue('tests.drupal.test-runner');
    $this->chromeDriverPort = $this->getConfigValue('tests.drupal.chromedriver.port');
    $this->chromeDriverArgs = $this->getConfigValue('tests.drupal.chromedriver.args');

    $this->browsertestOutputDirectory = $this->reportsDir . '/' . $this->getConfigValue('tests.drupal.browsertest_output_directory');
    $this->apacheRunUser = $this->getConfigValue('tests.drupal.apache_run_user');
    $this->sudoRunTests = $this->getConfigValue('tests.drupal.sudo_run_tests');

    $this->testingEnv = [
      self::APACHE_RUN_GROUP => $this->getConfigValue('tests.drupal.apache_run_user'),
      self::APACHE_RUN_USER => $this->apacheRunUser,
      self::BROWSERTEST_OUTPUT_DIRECTORY => $this->browsertestOutputDirectory,
      self::MINK_DRIVER_ARGS => $this->getConfigValue('tests.drupal.mink_driver_args'),
      self::MINK_DRIVER_ARGS_PHANTOMJS => $this->getConfigValue('tests.drupal.mink_driver_args_phantomjs'),
      self::MINK_DRIVER_ARGS_WEBDRIVER => $this->getConfigValue('tests.drupal.mink_driver_args_webdriver'),
      self::MINK_DRIVER_CLASS => $this->getConfigValue('tests.drupal.mink_driver_class'),
      self::SIMPLETEST_BASE_URL => $this->getConfigValue('tests.drupal.simpletest_base_url'),
      self::SIMPLETEST_DB => $this->getConfigValue('tests.drupal.simpletest_db'),
      self::SYMFONY_DEPRECATIONS_HELPER => $this->getConfigValue('tests.drupal.symfony_deprecations_helper'),
    ];
  }

  /**
   * Setup and run tests.
   */
  public function run() {
    try {
      $this->getTestingEnvString();
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
      $this->launchChromeDriver();
    }
  }

  /**
   * Kills the appropriate web driver based on configuration.
   */
  protected function killWebDriver() {
    if ($this->getConfigValue('tests.drupal.web-driver') == 'chromedriver') {
      $this->killChromeDriver();
    }
  }

  /**
   * Launches a headless chromedriver process.
   */
  protected function launchChromeDriver() {
    $this->killChromeDriver();
    $chromeDriverBin = $this->findChromeDriver();
    $chromeDriverHost = 'http://localhost';
    $this->logger->info("Launching chromedriver...");
    $this->getContainer()
      ->get('executor')
      ->execute("$chromeDriverBin")
      ->background(TRUE)
      ->printOutput(TRUE)
      ->printMetadata(TRUE)
      ->run();
    $this->getContainer()->get('executor')->waitForUrlAvailable("$chromeDriverHost:{$this->chromeDriverPort}");
  }

  /**
   * Kills headless chrome process running on $this->chromeDriverPort.
   */
  protected function killChromeDriver() {
    $this->logger->info("Killing running chromedriver processes...");
    $this->getContainer()->get('executor')->killProcessByPort($this->chromeDriverPort);
  }

  /**
   * Finds the local chromedriver binary.
   *
   * @return null|string
   *   NULL if Chrome could not be found.
   *
   * @throws \Acquia\Blt\Robo\Exceptions\BltException
   *   Throws exception if chromedriver cannot be found.
   */
  protected function findChromeDriver() {
    if ($this->getInspector()->commandExists('chromedriver')) {
      return 'chromedriver';
    }

    $osxPath = "/usr/local/bin/chromedriver";
    if ($this->getInspector()->isOsx() && file_exists($osxPath)) {
      return $osxPath;
    }

    throw new BltException("Could not find chromedriver.");
  }

  /**
   * Creates empty log directory and log file for PHPUnit tests.
   */
  protected function createLogs() {
    $this->taskFilesystemStack()
      ->mkdir($this->reportsDir)
      ->mkdir($this->browsertestOutputDirectory)
      ->touch($this->reportFile)
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
      ->run();
  }

}
