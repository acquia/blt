<?php

namespace Acquia\Blt\Robo\Commands\Tests;

use Acquia\Blt\Robo\Exceptions\BltException;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Defines commands in the "tests" namespace.
 */
class PhpUnitCommand extends DrupalTestCommand {

  /**
   * An array that contains configuration to override /
   * customize phpunit commands.
   *
   * @var array
   */
  protected $phpunitConfig;

  /**
   * This hook will fire for all commands in this command file.
   *
   * @hook init
   */
  public function initialize() {
    parent::initialize();
    $this->phpunitConfig = $this->getConfigValue('tests.phpunit');
  }

  /**
   * Setup and run tests.
   *
   * @command tests:phpunit:run
   * @aliases tpr phpunit tests:phpunit
   * @description Executes all PHPUnit tests. Launches chromedriver prior to execution.
   *
   * @interactGenerateSettingsFiles
   * @interactInstallDrupal
   * @validateMySqlAvailable
   * @validateDrupalIsInstalled
   * @validateVmConfig
   * @launchWebServer
   * @executeInVm
   */
  public function run() {
    if ($this->drupalTestRunner == 'phpunit') {
      parent::run();
    }
    else {
      try {
        $this->executeTests();
      }
      catch (\Exception $e) {
        throw $e;
      }
    }
  }

  /**
   * Executes all PHPUnit tests.
   */
  public function executeTests() {
    $this->createReportsDir();
    $this->createLogs();
    if (is_array($this->phpunitConfig)) {
      foreach ($this->phpunitConfig as $test) {
        $task = $this->taskPhpUnitTask()
          ->xml($this->reportFile)
          ->printOutput(TRUE)
          ->printMetadata(FALSE);

        if (isset($test['path'])) {
          $task->dir($test['path']);
        }

        if ($this->output()->getVerbosity() >= OutputInterface::VERBOSITY_NORMAL) {
          $task->printMetadata(TRUE);
          $task->verbose();
        }

        if (isset($this->testingEnvString)) {
          $task->testEnvVars($this->testingEnvString);
        }

        if (isset($this->apacheRunUser)) {
          $task->user($this->apacheRunUser);
        }

        if (isset($this->sudoRunTests) && ($this->sudoRunTests)) {
          $task->sudo();
        }

        if (isset($test['bootstrap'])) {
          $task->bootstrap($test['bootstrap']);
        }

        if (isset($test['config'])) {
          $task->configFile($test['config']);
        }

        if (isset($test['debug']) && ($test['debug'])) {
          $task->debug();
        }

        if (isset($test['exclude'])) {
          $task->excludeGroup($test['exclude']);
        }

        if (isset($test['filter'])) {
          $task->filter($test['filter']);
        }

        if (isset($test['group'])) {
          $task->group($test['group']);
        }

        if (isset($test['printer'])) {
          $task->printer($test['printer']);
        }

        if (isset($test['stop-on-error']) && ($test['stop-on-error'])) {
          $task->stopOnError();
        }

        if (isset($test['stop-on-failure']) && ($test['stop-on-failure'])) {
          $task->stopOnFailure();
        }

        if (isset($test['testdox']) && ($test['testdox'])) {
          $task->testdox();
        }

        if (isset($test['class'])) {
          $task->arg($test['class']);
          if (isset($test['file'])) {
            $task->arg($test['file']);
          }
        }
        else {
          if (isset($test['directory'])) {
            $task->arg($test['directory']);
          }
        }

        if ((isset($test['testsuites']) && is_array($test['testsuites'])) || isset($test['testsuite'])) {
          if (isset($test['testsuites'])) {
            $task->testsuite(implode(',', $test['testsuites']));
          }
          elseif (isset($test['testsuite'])) {
            $task->testsuite($test['testsuite']);
          }
        }

        $result = $task->run();
        $exit_code = $result->getExitCode();

        if ($exit_code) {
          throw new BltException("PHPUnit tests failed.");
        }
      }
    }
  }

}
