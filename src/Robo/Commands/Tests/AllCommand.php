<?php

namespace Acquia\Blt\Robo\Commands\Tests;

use Acquia\Blt\Robo\BltTasks;

/**
 * Defines commands in the "tests" namespace.
 */
class AllCommand extends BltTasks {

  /**
   * Runs all tests, including Behat, PHPUnit, and security updates check.
   *
   * @command tests
   *
   * @aliases tests:all
   * @executeInDrupalVm
   */
  public function tests() {
    $this->invokeCommands([
      'tests:behat',
      'tests:phpunit',
      'tests:security-updates',
      'frontend:test',
    ]);
  }

}
