<?php

namespace Acquia\Blt\Robo\Commands\Tests;

use Acquia\Blt\Robo\Wizards\TestsWizard;
use Robo\Contract\VerbosityThresholdInterface;

/**
 * Defines commands in the "tests" namespace.
 */
class BehatCommand extends TestsCommandBase {

  /**
   * The filename of the selenium log file.
   *
   * @var string*/
  protected $seleniumLogFile;

  /**
   * The URL at which Selenium server listens.
   *
   * @var string*/
  protected $seleniumUrl;

  /**
   * This hook will fire for all commands in this command file.
   *
   * @hook init
   */
  public function initialize() {
    parent::initialize();

    $this->seleniumLogFile = $this->getConfigValue('reports.localDir') . "/selenium2.log";
    $this->seleniumUrl = "http://127.0.0.1:4444/wd/hub";
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
   * @interactLaunchPhpWebServer
   * @interactGenerateSettingsFiles
   * @interactInstallDrupal
   * @interactConfigureBehat
   * @validateMySqlAvailable
   * @validateDrupalIsInstalled
   * @validateBehatIsConfigured
   * @validateInsideVm
   */
  public function behat() {
    // Log config for debugging purposes.
    $this->logConfig($this->getConfigValue('behat'), 'behat');
    $this->logConfig($this->getInspector()->getLocalBehatConfig()->export());
    $this->createReportsDir();
    $this->launchWebDriver();
    $this->executeBehatTests();
    $this->killWebDriver();
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
      ->execute($this->getConfigValue('composer.bin') . "/selenium-server-standalone -port 4444 -log {$this->seleniumLogFile}  > /dev/null 2>&1")
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
    $this->getContainer()->get('executor')->killProcessByPort('4444');
    $this->getContainer()->get('executor')->killProcessByName('selenium-server-standalone');
  }

  /**
   * Creates selenium log file.
   */
  protected function createSeleniumLogs() {
    $this->seleniumLogFile;
    $this->logger->info("Creating Selenium2 log file at {$this->seleniumLogFile}");
    $this->taskFilesystemStack()
      ->touch($this->seleniumLogFile)
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
      ->run();
  }

  /**
   * Launches selenium web driver.
   */
  protected function launchPhantomJs() {
    if (!$this->getInspector()->isPhantomJsConfigured()) {
      $this->setupPhantomJs();
    }
    $this->killPhantomJs();
    $this->say("Launching PhantomJS GhostDriver.");
    $this->taskExec("{$this->getConfigValue('composer.bin')}/phantomjs")
      ->option("webdriver", 4444)
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
      ->background()
      ->timeout(6000)
      ->silent(TRUE)
      ->run();
  }

  /**
   * Kills any running PhantomJS processes.
   */
  protected function killPhantomJs() {
    $this->getContainer()->get('executor')->killProcessByPort('4444');
    $this->getContainer()->get('executor')->killProcessByName('bin/phantomjs');
  }

  /**
   * Ensures that the PhantomJS binary is present.
   *
   * Sometimes the download fails during `composer install`.
   *
   * @command tests:configure-phantomjs
   *
   * @validatePhantomJsIsConfigured
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
        ->noInteraction()
        ->printMetadata(FALSE)
        ->stopOnFail()
        ->option('strict')
        ->option('config', $this->getConfigValue('behat.config'))
        ->option('profile', $this->getConfigValue('behat.profile'))
        ->option('tags', $this->getConfigValue('behat.tags'));
      // @todo Make verbose if blt.verbose is true.
      $task->detectInteractive();

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
