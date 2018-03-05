<?php

namespace Acquia\Blt\Robo\Commands\Tests;

use Acquia\Blt\Robo\Exceptions\BltException;
use Acquia\Blt\Robo\Wizards\TestsWizard;
use Robo\Contract\VerbosityThresholdInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Defines commands in the "tests" namespace.
 */
class BehatCommand extends TestsCommandBase {

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
   * The directory containing Behat logs.
   *
   * @var string
   */
  protected $behatLogDir;

  /**
   * @var string
   */
  protected $chromePort;

  /**
   * @var string
   */
  protected $chromeArgs;

  /**
   * This hook will fire for all commands in this command file.
   *
   * @hook init
   */
  public function initialize() {
    $this->seleniumLogFile = $this->getConfigValue('reports.localDir') . "/selenium2.log";
    $this->behatLogDir = $this->getConfigValue('reports.localDir') . "/behat";
    $this->chromePort = $this->getConfigValue('behat.chrome.port');
    $this->chromeArgs = $this->getConfigValue('behat.chrome.args');
    $this->seleniumPort = $this->getConfigValue('behat.selenium.port');
    $this->seleniumUrl = $this->getConfigValue('behat.selenium.url');
    $this->serverPort = $this->getConfigValue('tests.server.port');
    $this->serverUrl = $this->getConfigValue('tests.server.url');
  }

  /**
   * Executes all behat tests.
   *
   * @command tests:behat:run
   * @description Executes all behat tests. This optionally launch PhantomJS or Selenium prior to execution.
   * @usage
   *   Executes all configured tests.
   * @usage -D behat.paths=${PWD}/tests/behat/features/Examples.feature
   *   Executes scenarios in the Examples.feature file.
   * @usage -D behat.paths=${PWD}/tests/behat/features/Examples.feature:4
   *   Executes only the scenario on line 4 of Examples.feature.
   *
   * @aliases tbr behat tests:behat
   *
   * @interactGenerateSettingsFiles
   * @interactInstallDrupal
   * @interactConfigureBehat
   * @validateMySqlAvailable
   * @validateDrupalIsInstalled
   * @validateBehatIsConfigured
   * @validateVmConfig
   * @launchWebServer
   * @executeInVm
   */
  public function behat() {
    // Log config for debugging purposes.
    $this->logConfig($this->getConfigValue('behat'), 'behat');
    $this->logConfig($this->getInspector()->getLocalBehatConfig()->export());
    $this->createReportsDir();

    try {
      $this->launchWebDriver();
      $this->executeBehatTests();
      $this->killWebDriver();
    }
    catch (\Exception $e) {
      // Kill web driver a server to prevent Pipelines from hanging after fail.
      $this->killWebDriver();
      throw $e;
    }
  }

  /**
   * Lists available Behat step definitions.
   *
   * @command tests:behat:list:definitions
   *
   * @option mode l (default), i, or needle. Use l to just list definition expressions, i to show definitions with extended info, or needle to find specific definitions.
   *
   * @aliases tbd tests:behat:definitions
   *
   * @validateMySqlAvailable
   * @executeInVm
   */
  public function behatDefinitions($options = ['mode' => 'l']) {
    $task = $this->taskBehat($this->getConfigValue('composer.bin') . '/behat')
      ->format('pretty')
      ->noInteraction()
      ->printMetadata(FALSE)
      ->option('definitions', $options['mode'])
      ->option('config', $this->getConfigValue('behat.config'))
      ->option('profile', $this->getConfigValue('behat.profile'))
      ->interactive($this->input()->isInteractive());
    if ($this->output()->getVerbosity() >= OutputInterface::VERBOSITY_NORMAL) {
      $task->verbose();
    }

    if ($this->getConfigValue('behat.extra')) {
      $task->arg($this->getConfigValue('behat.extra'));
    }
    $result = $task->run();

    return $result;
  }

  /**
   * Launch the appropriate web driver based on configuration.
   */
  protected function launchWebDriver() {
    if ($this->getConfigValue('behat.web-driver') == 'phantomjs') {
      $this->launchPhantomJs();
    }
    elseif ($this->getConfigValue('behat.web-driver') == 'selenium') {
      $this->launchSelenium();
    }
    elseif ($this->getConfigValue('behat.web-driver') == 'chrome') {
      $this->launchChrome();
    }
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
      ->execute("'$chrome_bin' --headless --disable-web-security --remote-debugging-port={$this->chromePort} {$this->chromeArgs} $chrome_host")
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

    $osx_path = "/Applications/Google Chrome.app/Contents/MacOS/Google Chrome";
    if ($this->getInspector()->isOsx() && file_exists($osx_path)) {
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
      ->execute("'$bin' --version | cut -f3 -d' ' | cut -f1 -d'.'")
      ->run()
      ->getMessage();

    if ($version < 59) {
      throw new BltException("You must have Google Chrome version 59+ to execute headless tests.");
    }
  }

  /**
   * Kills the appropriate web driver based on configuration.
   */
  protected function killWebDriver() {
    if ($this->getConfigValue('behat.web-driver') == 'phantomjs') {
      $this->killPhantomJs();
    }
    elseif ($this->getConfigValue('behat.web-driver') == 'selenium') {
      $this->killSelenium();
    }
    elseif ($this->getConfigValue('behat.web-driver') == 'chrome') {
      $this->killChrome();
    }
  }

  /**
   * Launches selenium server and waits for it to become available.
   */
  protected function launchSelenium() {
    $this->createSeleniumLogs();
    $this->killSelenium();
    $this->logger->info("Launching Selenium standalone server...");
    $this->getContainer()
      ->get('executor')
      ->execute($this->getConfigValue('composer.bin') . "/selenium-server-standalone -port {$this->seleniumPort} -log {$this->seleniumLogFile}  > /dev/null 2>&1")
      ->background(TRUE)
      // @todo Print output when this command fails.
      ->printOutput(TRUE)
      ->dir($this->getConfigValue('repo.root'))
      ->run();
    $this->getContainer()->get('executor')->waitForUrlAvailable($this->seleniumUrl);
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

  /**
   * Launches selenium web driver.
   */
  protected function launchPhantomJs() {
    if (!$this->getInspector()->isPhantomJsBinaryPresent()) {
      $this->setupPhantomJs();
    }
    $this->killPhantomJs();
    $this->say("Launching PhantomJS GhostDriver...");
    $this->taskExec("'{$this->getConfigValue('composer.bin')}/phantomjs'")
      ->option("webdriver", $this->seleniumPort)
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
      ->background()
      ->timeout(6000)
      ->silent(TRUE)
      ->interactive(FALSE)
      ->run();
  }

  /**
   * Kills any running PhantomJS processes.
   */
  protected function killPhantomJs() {
    $this->getContainer()->get('executor')->killProcessByPort($this->seleniumPort);
    $this->getContainer()->get('executor')->killProcessByName('bin/phantomjs');
  }

  /**
   * Ensures that the PhantomJS binary is present.
   *
   * Sometimes the download fails during `composer install`.
   *
   * @command tests:behat:init:phantomjs
   * @aliases tbip
   */
  public function setupPhantomJs() {
    /** @var \Acquia\Blt\Robo\Wizards\TestsWizard $tests_wizard */
    $tests_wizard = $this->getContainer()->get(TestsWizard::class);
    $tests_wizard->wizardInstallPhantomJsBinary();
  }

  /**
   * Executes all behat tests in behat.paths configuration array.
   *
   * @throws \Exception
   *   Throws an exception if any Behat test fails.
   */
  protected function executeBehatTests() {
    $behat_paths = $this->getConfigValue('behat.paths');
    if (is_string($behat_paths)) {
      $behat_paths = [$behat_paths];
    }

    foreach ($behat_paths as $behat_path) {
      // If we do not have an absolute path, we assume that the behat feature
      // path is relative to tests/behat/features.
      if (!$this->getInspector()->getFs()->isAbsolutePath($behat_path)) {
        $behat_path = $this->getConfigValue('repo.root') . '/tests/behat/features/' . $behat_path;
      }
      // Output errors.
      // @todo replace base_url in behat config when internal server is being used.
      $task = $this->taskBehat($this->getConfigValue('composer.bin') . '/behat')
        ->format('pretty')
        ->arg($behat_path)
        ->option('colors')
        ->noInteraction()
        ->printMetadata(FALSE)
        ->stopOnFail()
        ->option('strict')
        ->option('config', $this->getConfigValue('behat.config'))
        ->option('profile', $this->getConfigValue('behat.profile'))
        ->option('tags', $this->getConfigValue('behat.tags'))
        ->interactive($this->input()->isInteractive());

      if ($this->output()->getVerbosity() >= OutputInterface::VERBOSITY_NORMAL) {
        $task->verbose();
      }

      if ($this->getConfigValue('behat.extra')) {
        $task->arg($this->getConfigValue('behat.extra'));
      }

      $result = $task->run();

      if (!$result->wasSuccessful()) {
        throw new BltException("Behat tests failed!");
      }
    }
  }

}
