<?php

namespace Acquia\Blt\Robo\Commands\Tests;

use Acquia\Blt\Robo\BltTasks;

/**
 * Defines commands in the "tests" namespace.
 */
class AllCommand extends BltTasks {

  /**
   * Runs all tests, including Behat, PHPUnit, and Security Update check.
   *
   * @command tests
   *
   * @aliases tests:all, test
   *
   * @executeInDrupalVm
   */
  public function tests() {
    $status_code = $this->invokeCommands([
      'tests:behat',
      'tests:phpunit',
      'tests:security-updates',
    ]);

    return $status_code;
  }

}
