<?php

namespace Acquia\Blt\Robo\Commands\Tests;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Exceptions\BltException;
use Acquia\Blt\Robo\Tasks\PHPUnitTask;
use Robo\Contract\VerbosityThresholdInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Defines commands in the "tests" namespace.
 */
class RunTestsCommand extends DrupalTestCommand {

  /**
   * An array that contains configuration to override /
   * customize run-tests.sh commands.
   *
   * @var array
   */
  protected $runTestsConfig;

  /**
   * The command to run Drupal's run-tests.sh script.
   *
   * @var string
   */
  protected $runTestsScriptCommand;

  /**
   * This hook will fire for all commands in this command file.
   *
   * @hook init
   */
  public function initialize() {
    parent::initialize();
    $this->runTestsConfig = $this->getConfigValue('tests.run-tests');
    $this->runTestsScriptCommand = './core/scripts/run-tests.sh';
  }

  /**
   * Executes the Drupal run-tests.sh script.
   *
   * @command tests:run-tests:run
   * @aliases tdrtr
   */
  public function runTests() {
    $this->createReportsDir();
    foreach ($this->runTestsConfig as $test) {
      $task = $this->taskRunTestsTask($this->runTestsScriptCommand)
        ->dir($this->getConfigValue('docroot'))
        ->xml($this->reportsDir)
        ->printOutput(TRUE)
        ->printMetadata(FALSE);

      if ($this->output()->getVerbosity() >= OutputInterface::VERBOSITY_NORMAL) {
        $task->printMetadata(TRUE);
        $task->verbose();
      }

      if (isset($test['all']) && ($test['all'])) {
        $task->all();
      }

      if (isset($test['browser']) && ($test['browser'])) {
        $task->browser();
      }

      if (isset($test['clean']) && ($test['clean'])) {
        $task->clean();
      }

      if (isset($test['color']) && ($test['color'])) {
        $task->color();
      }

      if (isset($test['die-on-fail']) && ($test['die-on-fail'])) {
        $task->dieOnFail();
      }

      if (isset($test['keep-results']) && ($test['keep-results'])) {
        $task->keepResults();
      }

      if (isset($test['keep-results-table']) && ($test['keep-results-table'])) {
        $task->keepResultsTable();
      }

      if (isset($test['suppress-deprecations']) && ($test['suppress-deprecations'])) {
        $task->suppressDeprecations();
      }

      if (isset($test['concurrency']) && is_int($test['concurrency'])) {
        $task->concurrency($test['concurrency']);
      }

      if (isset($test['repeat']) && is_int($test['repeat'])) {
        $task->repeat($test['repeat']);
      }

      if (isset($test['dburl'])) {
        $task->dbUrl($test['dburl']);
      }

      if (isset($test['sqlite'])) {
        $task->sqlite($test['sqlite']);
      }

      if (isset($test['url'])) {
        $task->url($test['url']);
      }

      if ( (isset($test['types']) && is_array($test['types'])) || isset($test['type']) ) {
        if (isset($test['types'])) {
          $task->types(implode(',', $test['types']));
        }
        elseif (isset($test['type'])) {
          $task->types($test['type']);
        }
      }

      if (isset($test['tests']) && is_array($test['tests'])) {
        $task->tests($test['tests']);
      }

      $result = $task->run();
      $exit_code = $result->getExitCode();

      if ($exit_code) {
        throw new BltException("Drupal tests failed.");
      }
    }
  }

}