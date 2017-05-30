<?php

namespace Acquia\Blt\Robo\Commands\Tests;

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
   * This hook will fire for all commands in this command file.
   *
   * @hook init
   */
  public function initialize() {
    $this->seleniumLogFile = $this->getConfigValue('reports.localDir') . "/selenium2.log";
    $this->behatLogDir = $this->getConfigValue('reports.localDir') . "/behat";
    $this->seleniumPort = $this->getConfigValue('behat.selenium.port');
    $this->seleniumUrl = $this->getConfigValue('behat.selenium.url');
    $this->serverPort = $this->getConfigValue('behat.server.port');
    $this->serverUrl = $this->getConfigValue('behat.server.url');
  }

  /**
   * Executes all behat tests.
   *
   * @command tests:behat
   * @description Executes all behat tests. This optionally launch PhantomJS or Selenium prior to execution.
   * @usage
   *   Executes all configured tests.
   * @usage -D behat.paths=${PWD}/tests/behat/features/Examples.feature
   *   Executes scenarios in the Examples.feature file.
   * @usage -D behat.paths=${PWD}/tests/behat/features/Examples.feature:4
   *   Executes only the scenario on line 4 of Examples.feature.
   *
   * @interactGenerateSettingsFiles
   * @interactInstallDrupal
   * @interactConfigureBehat
   * @validateMySqlAvailable
   * @validateDrupalIsInstalled
   * @validateBehatIsConfigured
   * @validateInsideVm
   * @executeInDrupalVm
   */
  public function behat() {
    // Log config for debugging purposes.
    $this->logConfig($this->getConfigValue('behat'), 'behat');
    $this->logConfig($this->getInspector()->getLocalBehatConfig()->export());
    $this->createReportsDir();
    $this->launchWebServer();
    $this->launchWebDriver();
    $this->executeBehatTests();
    $this->killWebDriver();
    $this->killWebServer();
  }

  /**
   * Lists available Behat step definitions.
   *
   * @command tests:behat:definitions
   * @aliases tbd
   *
   * @option mode l (default), i, or needle. Use l to just list definition expressions, i to show definitions with extended info, or needle to find specific definitions.
   *
   * @validateMySqlAvailable
   */
  public function behatDefinitions($options = ['mode' => 'l']) {
    $task = $this->taskBehat($this->getConfigValue('composer.bin') . '/behat')
      ->format('pretty')
      ->noInteraction()
      ->printMetadata(FALSE)
      ->option('definitions', $options['mode'])
      ->option('config', $this->getConfigValue('behat.config'))
      ->option('profile', $this->getConfigValue('behat.profile'))
      ->detectInteractive();
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
   * Launches PHP's internal web server via `drush run-server`.
   */
  protected function launchWebServer() {
    if ($this->getConfigValue('behat.run-server')) {
      $this->killWebServer();
      $this->say("Launching PHP's internal web server via drush.");
      $this->logger->info("Running server at $this->serverUrl...");
      $this->getContainer()->get('executor')->drush("runserver $this->serverUrl > /dev/null")->background(TRUE)->run();
      $this->getContainer()->get('executor')->waitForUrlAvailable($this->serverUrl);
    }
  }

  /**
   * Kills PHP internal web server running on $this->serverUrl.
   */
  protected function killWebServer() {
    $this->getContainer()->get('executor')->killProcessByName('runserver');
    $this->getContainer()->get('executor')->killProcessByPort($this->serverPort);
  }

  /**
   * Launch the appropriate web driver based on configuration.
   */
  protected function launchWebDriver() {
    if ($this->getConfigValue('behat.launch-phantomjs')) {
      $this->launchPhantomJs();
    }
    elseif ($this->getConfigValue('behat.launch-selenium')) {
      $this->launchSelenium();
    }
  }

  /**
   * Kills the appropriate web driver based on configuration.
   */
  protected function killWebDriver() {
    if ($this->getConfigValue('behat.launch-phantomjs')) {
      $this->killPhantomJs();
    }
    elseif ($this->getConfigValue('behat.launch-selenium')) {
      $this->killSelenium();
    }
  }

  /**
   * Launches selenium server and waits for it to become available.
   */
  protected function launchSelenium() {
    $this->createSeleniumLogs();
    $this->killSelenium();
    $this->logger->info("Launching Selenium standalone server.");
    $this->getContainer()
      ->get('executor')
      ->execute($this->getConfigValue('composer.bin') . "/selenium-server-standalone -port {$this->seleniumPort} -log {$this->seleniumLogFile}  > /dev/null 2>&1")
      ->background(TRUE)
      ->printOutput(TRUE)
      ->dir($this->getConfigValue('repo.root'))
      ->run();
    $this->getContainer()->get('executor')->waitForUrlAvailable($this->seleniumUrl);
  }

  /**
   * Kills any Selenium processes already running.
   */
  protected function killSelenium() {
    $this->logger->info("Killing any running Selenium processes");
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
   * @command tests:configure-phantomjs
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
    foreach ($this->getConfigValue('behat.paths') as $behat_path) {
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
        ->detectInteractive();

      if ($this->output()->getVerbosity() >= OutputInterface::VERBOSITY_NORMAL) {
        $task->verbose();
      }

      if ($this->getConfigValue('behat.extra')) {
        $task->arg($this->getConfigValue('behat.extra'));
      }

      $result = $task->run();

      if (!$result->wasSuccessful()) {
        throw new \Exception("Behat tests failed");
      }
    }
  }

}
