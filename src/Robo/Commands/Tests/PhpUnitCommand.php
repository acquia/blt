<?php

namespace Acquia\Blt\Robo\Commands\Tests;

use Acquia\Blt\Robo\BltTasks;
use Robo\Contract\VerbosityThresholdInterface;

/**
 * Defines commands in the "tests" namespace.
 */
class PhpUnitCommand extends BltTasks {


  /**
   * Directory in which test logs and reports are generated.
   *
   * @var string*/
  protected $reportsDir;

  /**
   * The filename for PHPUnit report.
   *
   * @var string*/
  protected $reportFile;

  /**
   * The directory path containing PHPUnit tests.
   *
   * @var string*/
  protected $testsDir;

  /**
   * Wether or not to include the Drupal config file for PHPUnit.
   *
   * @var bool*/
  protected $configStatus;

  /**
   * The path to Drupal's configuration file for PHPUnit.
   *
   * @var string*/
  protected $configFile;

  /**
   * This hook will fire for all commands in this command file.
   *
   * @hook init
   */
  public function initialize() {
    $this->reportsDir = $this->getConfigValue('reports.localDir') . '/phpunit';
    $this->reportFile = $this->reportsDir . '/results.xml';
    $this->testsDir = $this->getConfigValue('repo.root') . '/tests/phpunit';
    $this->configStatus = $this->getConfigValue('phpunit.config');
    $this->configFile = $this->getConfigValue('repo.root') . '/docroot/core/phpunit.xml.dist';
  }

  /**
   * Executes all PHPUnit tests.
   *
   * @command tests:phpunit
   * @description Executes all PHPUnit tests.
   */
  public function testsPhpUnit() {
    $this->createLogs();
    $task = $this->taskPHPUnit()
      ->dir($this->testsDir)
      ->xml($this->reportFile)
      ->arg('.')
      ->printOutput(TRUE)
      ->printMetadata(FALSE);
    if ($this->configStatus == TRUE) {
      $task->option('-c', $this->configFile);
    }
    $task->run();
  }

  /**
   * Creates empty log directory and log file for PHPUnit tests.
   */
  protected function createLogs() {
    $this->taskFilesystemStack()
      ->mkdir($this->reportsDir)
      ->touch($this->reportFile)
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
      ->run();
  }

}
