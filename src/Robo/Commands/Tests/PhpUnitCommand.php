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
   * This hook will fire for all commands in this command file.
   *
   * @hook init
   */
  public function initialize() {
    parent::initialize();

    $this->reportsDir = $this->getConfigValue('reports.localDir') . '/phpunit';
    $this->reportFile = $this->reportsDir . '/results.xml';
    $this->testsDir = $this->getConfigValue('repo.root') . '/tests/phpunit';
  }

  /**
   * Executes all PHPUnit tests.
   *
   * @command tests:phpunit
   * @description Executes all PHPUnit tests.
   */
  public function testsPhpUnit() {
    $this->createLogs();
    $this->taskPHPUnit()
      ->dir($this->testsDir)
      ->xml($this->reportFile)
      ->arg('.')
      ->printOutput(TRUE)
      ->printMetadata(FALSE)
      ->run();
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
