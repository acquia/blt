<?php

namespace Acquia\Blt\Robo\Commands\Tests;

use Acquia\Blt\Robo\Exceptions\BltException;
use Acquia\Blt\Robo\Wizards\TestsWizard;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Defines commands in the "tests" namespace.
 */
class BehatCommand extends TestsCommandBase {

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
    parent::initialize();
    $this->behatLogDir = $this->getConfigValue('tests.reports.localDir') . "/behat";
  }

  /**
   * Entrypoint for running behat tests.
   *
   * @command tests:behat:run
   * @description Executes all behat tests. This optionally launch Selenium
   *   prior to execution.
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
   * @validateDrupalIsInstalled
   * @validateVmConfig
   * @launchWebServer
   * @executeInVm
   *
   * @throws \Acquia\Blt\Robo\Exceptions\BltException
   * @throws \Exception
   */
  public function behat() {
    if ($this->getConfigValue('behat.validate')) {
      /** @var \Acquia\Blt\Robo\Wizards\TestsWizard $tests_wizard */
      $tests_wizard = $this->getContainer()->get(TestsWizard::class);
      $tests_wizard->wizardConfigureBehat();
      if (!$this->getInspector()->isBehatConfigured()) {
        throw new BltException("Behat is not configured properly. Please run `blt doctor` to diagnose the issue.");
      }
    }

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
    if ($this->getConfigValue('behat.web-driver') == 'selenium') {
      $this->launchSelenium();
    }
    elseif ($this->getConfigValue('behat.web-driver') == 'chrome') {
      $this->launchChrome();
    }
  }

  /**
   * Kills the appropriate web driver based on configuration.
   */
  protected function killWebDriver() {
    if ($this->getConfigValue('behat.web-driver') == 'selenium') {
      $this->killSelenium();
    }
    elseif ($this->getConfigValue('behat.web-driver') == 'chrome') {
      $this->killChrome();
    }
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
