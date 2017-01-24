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
class AllCommand extends BltTasks {

  /**
   * Runs all tests, including Behat, PHPUnit, and Security Update check.
   *
   * @command tests:all
   *
   * @calls tests:behat, tests:phpunit
   */
  public function tests() {
  }

}
