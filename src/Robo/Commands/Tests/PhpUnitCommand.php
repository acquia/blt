<?php

namespace Acquia\Blt\Robo\Commands\Tests;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Exceptions\BltException;
use Robo\Contract\VerbosityThresholdInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
   * An array that contains configuration to override /
   * customize phpunit commands.
   *
   * @var array*/
  protected $phpunitConfig;

  /**
   * This hook will fire for all commands in this command file.
   *
   * @hook init
   */
  public function initialize() {
    $this->reportsDir = $this->getConfigValue('reports.localDir') . '/phpunit';
    $this->reportFile = $this->reportsDir . '/results.xml';
    $this->testsDir = $this->getConfigValue('repo.root') . '/tests/phpunit';
    $this->phpunitConfig = $this->getConfigValue('phpunit');
  }

  /**
   * Executes all PHPUnit tests.
   *
   * @command tests:phpunit:run
   * @aliases tpr phpunit tests:phpunit
   */
  public function testsPhpUnit() {
    $this->createLogs();
    foreach ($this->phpunitConfig as $test) {
      $task = $this->taskPHPUnit()
        ->xml($this->reportFile)
        ->printOutput(TRUE)
        ->printMetadata(FALSE);

      if (isset($test['class'])) {
        $task->arg($test['class']);
        if (isset($test['file'])) {
          $task->arg($test['file']);
        }
      }
      else {
        if (isset($test['path'])) {
          $task->arg($test['path']);
        }
      }

      if (isset($test['path'])) {
        $task->dir($test['path']);
      }

      if ($this->output()->getVerbosity() >= OutputInterface::VERBOSITY_NORMAL) {
        $task->printMetadata(TRUE);
        $task->arg('-v');
      }

      $supported_options = [
        'config' => 'configuration',
        'exclude-group' => 'exclude-group',
        'filter' => 'filter',
        'group' => 'group',
        'testsuite' => 'testsuite',
      ];

      foreach ($supported_options as $yml_key => $option) {
        if (isset($test[$yml_key])) {
          $task->option("--$option", $test[$yml_key]);
        }
      }

      $result = $task->run();
      $exit_code = $result->getExitCode();

      if ($exit_code) {
        throw new BltException("PHPUnit tests failed.");
      }
    }
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
