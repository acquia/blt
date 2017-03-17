<?php

namespace Acquia\Blt\Robo\Commands\Tests;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Wizards\TestsWizard;
use Drupal\Core\Database\Log;
use GuzzleHttp\Client;
use Psr\Log\LogLevel;
use Robo\Contract\VerbosityThresholdInterface;
use Wikimedia\WaitConditionLoop;

/**
 * Defines commands in the "tests" namespace.
 */
class BehatCommand extends BltTasks {

  /** @var string  */
  protected $seleniumLogFile;

  /** @var string */
  protected $seleniumUrl;

  /**
   * This hook will fire for all commands in this command file.
   *
   * @hook init
   */
  public function initialize() {
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
  public function behat() {
    // Log config for debugging purposes.
    $this->logConfig($this->getConfigValue('behat'), 'behat');
    $this->logConfig($this->getInspector()->getLocalBehatConfig()->toArray());
    $this->createReportsDir();

    // Launch the appropriate web driver.
    if ($this->getConfigValue('behat.launch-phantomjs')) {
      $this->launchPhantomJs();
    }
    elseif ($this->getConfigValue('behat.launch-selenium')) {
      $this->launchSelenium();
    }

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
   *
   */
  protected function createReportsDir() {
    // Create reports dir.
    $logs_dir = $this->getConfigValue('reports.localDir');
    $this->taskFilesystemStack()
      ->mkdir($logs_dir)
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
      ->run();
  }

  /**
   * Launches selenium server.
   */
  protected function launchSelenium() {
    $this->createSeleniumLogs();
    $this->getContainer()->get('executor')->killProcessByPort('4444');
    $this->getContainer()->get('executor')->killProcessByName('selenium');
    $this->say("Launching Selenium standalone server.");
    $this->getContainer()
      ->get('executor')
      ->execute($this->getConfigValue('composer.bin') . "/selenium-server-standalone -port 4444 -log {$this->seleniumLogFile}  > /dev/null 2>&1")
      ->background(true)
      ->printOutput(true)
      ->dir($this->getConfigValue('repo.root'))
      ->run();
    $this->getContainer()->get('executor')->waitForUrlAvailable($this->seleniumUrl);
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

    $this->getContainer()->get('executor')->killProcessByPort('4444');
    $this->getContainer()->get('executor')->killProcessByName('phantomjs');
    $this->say("Launching PhantomJS GhostDriver.");
    $this->taskExec("{$this->getConfigValue('composer.bin')}/phantomjs")
      ->option("webdriver", 4444)
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
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
