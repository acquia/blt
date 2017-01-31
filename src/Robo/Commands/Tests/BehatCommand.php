<?php

namespace Acquia\Blt\Robo\Commands\Tests;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Wizards\TestsWizard;
use Drupal\Core\Database\Log;
use GuzzleHttp\Client;
use Psr\Log\LogLevel;
use Wikimedia\WaitConditionLoop;

/**
 * Defines commands in the "tests" namespace.
 */
class BehatCommand extends BltTasks {

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
    $this->logConfig($this->getConfigValue('behat'), 'behat');
    $this->logConfig($this->getInspector()->getLocalBehatConfig()->toArray());

    if ($this->getConfigValue('behat.launch-phantomjs')) {
      $this->launchPhantomJs();
    }
    elseif ($this->getConfigValue('behat.launch-selenium')) {
      $this->launchSelenium();
    }

    $logs_dir = $this->getConfigValue('reports.localDir');
    $this->logger->info("Creating Behat log files at $logs_dir");
    $this->taskFilesystemStack()
      ->mkdir($logs_dir)
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
   *
   */
  protected function launchSelenium() {
    $log_file = $this->getConfigValue('reports.localDir') . "/selenium2.log";
    $this->logger->info("Creating Selenium2 log file at $log_file");
    $this->_touch($log_file);
    $this->getContainer()->get('executor')->killProcessByPort('4444');
    $this->getContainer()->get('executor')->killProcessByName('selenium');
    $this->say("Launching Selenium standalone server.");
    $this->getContainer()->get('executor')->execute($this->getConfigValue('composer.bin') . "/selenium-server-standalone -port 4444 -log $log_file  > /dev/null 2>&1")
      ->background(true)
      ->printOutput(true)
      //->setLogLevel(LogLevel::DEBUG)
      ->dir($this->getConfigValue('repo.root'))
      ->run();
    $url = "http://127.0.0.1:4444/wd/hub";
    $this->getContainer()->get('executor')->waitForUrlAvailable($url);
  }

  /**
   *
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
