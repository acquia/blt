<?php

namespace Acquia\Blt\Robo\Commands\Tests;

use Acquia\Blt\Robo\BltTasks;
use Symfony\Component\Console\Input\ArrayInput;

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

  /**
   * Invokes an array of Symfony commands.
   *
   * @return int
   *   The exit code of the command.
   */
  public function invokeCommands($commands) {
    foreach ($commands as $command) {
      $returnCode = $this->invokeCommand($command);
      // Return if this is non-zero exit code.
      if ($returnCode) {
        return $returnCode;
      }
    }
  }

  /**
   * Invokes a single Symfony command.
   *
   * @param string $command_name
   *   The name of the command. E.g., 'tests:behat'.
   *
   * @return int
   *   The exit code of the command.
   */
  public function invokeCommand($command_name) {
    /** @var \Robo\Application $application */
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
