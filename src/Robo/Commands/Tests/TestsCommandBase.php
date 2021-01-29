<?php

namespace Acquia\Blt\Robo\Commands\Tests;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Common\EnvironmentDetector;
use Acquia\Blt\Robo\Exceptions\BltException;
use Robo\Contract\VerbosityThresholdInterface;

/**
 * Defines commands in the "tests" namespace.
 */
class TestsCommandBase extends BltTasks {

  /**
   * @var string
   */
  protected $chromePort;

  /**
   * @var string
   */
  protected $chromeArgs;

  /**
   * @var string
   */
  protected $chromeDriverPort;

  /**
   * @var string
   */
  protected $chromeDriverArgs;

  /**
   * The filename of the selenium log file.
   *
   * @var string
   */
  protected $seleniumLogFile;

  /**
   * The URL at which Selenium server listens.
   *
   * @var string
   */
  protected $seleniumUrl;

  /**
   * @var int
   */
  protected $seleniumPort;

  /**
   * @var string
   */
  protected $serverUrl;

  /**
   * @var int
   */
  protected $serverPort;

  /**
   * This hook will fire for all commands in this command file.
   *
   * @hook init
   */
  public function initialize() {
    // Log config for debugging purposes.
    $this->logConfig($this->getConfigValue('tests'), 'tests');

    $this->chromePort = $this->getConfigValue('tests.chrome.port');
    $this->chromeArgs = $this->getConfigValue('tests.chrome.args');
    $this->chromeDriverPort = $this->getConfigValue('tests.chromedriver.port');
    $this->chromeDriverArgs = $this->getConfigValue('tests.chromedriver.args');
    $this->seleniumPort = $this->getConfigValue('tests.selenium.port');
    $this->seleniumUrl = $this->getConfigValue('tests.selenium.url');
    $this->seleniumLogFile = $this->getConfigValue('tests.reports.localDir') . "/selenium2.log";
    $this->serverPort = $this->getConfigValue('tests.server.port');
    $this->serverUrl = $this->getConfigValue('tests.server.url');
  }

  /**
   * Creates the reports directory, if it does not exist.
   */
  protected function createReportsDir() {
    // Create reports dir.
    $logs_dir = $this->getConfigValue('tests.reports.localDir');
    $this->taskFilesystemStack()
      ->mkdir($logs_dir)
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
      ->run();
  }

  /**
   * Launches a headless chrome process.
   */
  protected function launchChrome() {
    $this->killChrome();
    $chrome_bin = $this->findChrome();
    $this->checkChromeVersion($chrome_bin);
    $chrome_host = 'http://localhost';
    $this->logger->info("Launching headless chrome...");
    $this->getContainer()
      ->get('executor')
      ->execute("'$chrome_bin' --headless --no-sandbox --disable-web-security --remote-debugging-port={$this->chromePort} {$this->chromeArgs} $chrome_host")
      ->background(TRUE)
      ->printOutput(TRUE)
      ->printMetadata(TRUE)
      ->run();
    $this->getContainer()->get('executor')->waitForUrlAvailable("$chrome_host:{$this->chromePort}");
  }

  /**
   * Kills headless chrome process running on $this->chromePort.
   */
  protected function killChrome() {
    $this->logger->info("Killing running google-chrome processes...");
    $this->getContainer()->get('executor')->killProcessByPort($this->chromePort);
  }

  /**
   * Finds the local Chrome binary.
   *
   * @return null|string
   *   NULL if Chrome could not be found.
   *
   * @throws \Acquia\Blt\Robo\Exceptions\BltException
   *   Throws exception if google-chrome cannot be found.
   */
  protected function findChrome() {
    if ($this->getInspector()->commandExists('google-chrome')) {
      return 'google-chrome';
    }

    if ($this->getInspector()->commandExists('chromium-browser')) {
      return 'chromium-browser';
    }

    $osx_path = "/Applications/Google Chrome.app/Contents/MacOS/Google Chrome";
    if (EnvironmentDetector::isDarwin() && file_exists($osx_path)) {
      return $osx_path;
    }

    throw new BltException("Could not find Google Chrome. Please add an alias for \"google-chrome\" to your CLI environment.");
  }

  /**
   * Verifies that Google Chrome meets minimum version requirement.
   *
   * @param string $bin
   *   Absolute file path to the google chrome bin.
   *
   * @throws \Acquia\Blt\Robo\Exceptions\BltException
   *   Throws exception if minimum version is not met.
   */
  protected function checkChromeVersion($bin) {
    $version = (int) $this->getContainer()->get('executor')
      ->execute("'$bin' --version | cut -f1 -d'.' | rev | cut -f1 -d' ' | rev")
      ->run()
      ->getMessage();

    if ($version < 59) {
      throw new BltException("You must have Google Chrome version 59+ to execute headless tests.");
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
    if (EnvironmentDetector::isDarwin() && file_exists($osxPath)) {
      return $osxPath;
    }

    throw new BltException("Could not find chromedriver.");
  }

  /**
   * Launches selenium server and waits for it to become available.
   *
   * @throws \Acquia\Blt\Robo\Exceptions\BltException
   */
  protected function launchSelenium() {
    $this->createSeleniumLogs();
    $this->killSelenium();
    $this->logger->info("Launching Selenium standalone server...");
    $selenium_bin = $this->getConfigValue('composer.bin') . '/selenium-server-standalone';
    if (!file_exists($selenium_bin)) {
      throw new BltException("Could not find Selenium. Install it via `composer require se/selenium-server-standalone`.");
    }
    $log_file = $this->getConfigValue('repo.root') . '/tmp/selenium.log';
    /** @var Acquia\Blt\Robo\Common\Executor $executor */
    $executor = $this->getContainer()->get('executor');
    $result = $executor
      ->execute("$selenium_bin -port {$this->seleniumPort} -log {$this->seleniumLogFile}  > $log_file 2>&1")
      ->background(TRUE)
      // @todo Print output when this command fails.
      ->printOutput(TRUE)
      ->dir($this->getConfigValue('repo.root'))
      ->run();
    try {
      $executor->waitForUrlAvailable($this->seleniumUrl);
    }
    catch (\Exception $e) {
      if (!$result->wasSuccessful()) {
        $message = $e->getMessage();
        if (file_exists($log_file)) {
          $message .= "\n\nThe following errors were logged:\n" . file_get_contents($log_file);
        }
        if (file_exists($this->seleniumLogFile)) {
          $message .= "\n\nSelenium internal logs:\n" . file_get_contents($this->seleniumLogFile);
        }
        throw new BltException($message);
      }
    }
  }

  /**
   * Kills any Selenium processes already running.
   */
  protected function killSelenium() {
    $this->logger->info("Killing any running Selenium processes...");
    $this->getContainer()->get('executor')->killProcessByPort($this->seleniumPort);
    $this->getContainer()->get('executor')->killProcessByName('selenium-server-standalone');
  }

  /**
   * Creates selenium log file.
   */
  protected function createSeleniumLogs() {
    $this->seleniumLogFile;
    $this->logger->info("Creating Selenium2 log file at {$this->seleniumLogFile}...");
    $this->taskFilesystemStack()
      ->touch($this->seleniumLogFile)
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
      ->run();
  }

}
