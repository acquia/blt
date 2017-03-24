<?php

namespace Acquia\Blt\Robo\Commands\Tests;

use Acquia\Blt\Robo\Blt;
use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Wizards\TestsWizard;
use Drupal\Core\Database\Log;
use GuzzleHttp\Client;
use Psr\Log\LogLevel;
use Robo\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Wikimedia\WaitConditionLoop;

/**
 * Defines commands in the "tests" namespace.
 */
class AllCommand extends BltTasks {

  /**
   * Runs all tests, including Behat, PHPUnit, and Security Update check.
   *
   * @command tests:all
   */
  public function tests() {
    $this->invokeCommands([
      'tests:behat',
      'tests:phpunit',
      'tests:security-updates',
    ]);
  }

  public function invokeCommands($commands) {
    foreach ($commands as $command) {
      $returnCode = $this->invokeCommand($command);
      // Return if this is non-zero exit code.
      if ($returnCode) {
        return $returnCode;
      }
    }
  }

  public function invokeCommand($command_name) {
    /** @var Application $application */
    $application = $this->getContainer()->get('application');
    $command = $application->find($command_name);
    $args = [];
    $input = new ArrayInput($args);
    $this->output->writeln("<comment>$command_name ></comment>");
    $returnCode = $command->run($input, $this->output());
    $this->output->writeln("");

    return $returnCode;
  }

}
