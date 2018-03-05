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
   * @aliases ta test tests:all
   */
  public function tests() {
    $this->invokeCommands([
      'tests:behat:run',
      'tests:phpunit:run',
      'tests:security:check:updates',
      'tests:frontend:run',
    ]);
  }

}
