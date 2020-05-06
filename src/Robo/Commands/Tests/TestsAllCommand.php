<?php

namespace Acquia\Blt\Robo\Commands\Tests;

use Acquia\Blt\Robo\BltTasks;

/**
 * Defines commands in the "tests" namespace.
 */
class TestsAllCommand extends BltTasks {

  /**
   * Runs all tests, including Behat, PHPUnit, and security updates check.
   *
   * @command tests
   *
   * @throws \Acquia\Blt\Robo\Exceptions\BltException
   */
  public function tests() {
    return $this->invokeNamespace('tests');
  }

}
