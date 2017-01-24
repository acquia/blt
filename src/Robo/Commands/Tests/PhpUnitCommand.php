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
class PhpUnitCommand extends BltTasks {

  /**
   * Executes all PHPUnit tests.
   *
   * @command tests:phpunit
   * @description Executes all PHPUnit tests.
   */
  public function testsPhpUnit() {
    $reports_dir = $this->getConfigValue('reports.localDir') . '/phpunit';
    $report_file = $reports_dir . '/results.xml';
    $this->_mkdir($reports_dir);
    $this->_touch($report_file);
    $tests_dir = $this->getConfigValue('repo.root') . '/tests/phpunit';

    $this->taskPHPUnit()
      ->dir($tests_dir)
      ->xml($report_file)
      ->arg('.')
      ->printOutput(true)
      ->run();
  }

}
