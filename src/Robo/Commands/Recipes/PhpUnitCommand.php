<?php

namespace Acquia\Blt\Robo\Commands\Recipes;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Exceptions\BltException;
use Robo\Contract\VerbosityThresholdInterface;

/**
 * Defines commands in the "recipes:phpunit:*" namespace.
 */
class PhpUnitCommand extends BltTasks {

  /**
   * Generates example files for writing PHPUnit tests.
   *
   * @command recipes:phpunit:init
   *
   * @throws \Acquia\Blt\Robo\Exceptions\BltException
   */
  public function init() {
    $result = $this->taskFilesystemStack()
      ->copy(
        $this->getConfigValue('blt.root') . '/scripts/blt/examples/Test/ExampleTest.php',
        $this->getConfigValue('repo.root') . '/tests/phpunit/ExampleTest.php', FALSE)
      ->stopOnFail()
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
      ->run();

    if (!$result->wasSuccessful()) {
      throw new BltException("Could not copy example files into the repository root.");
    }

    $this->taskExec("composer require --dev --no-update phpunit/phpunit:'^4.8.35 || ^6.5 || ^7'")
      ->run();
    $this->taskExec("composer update")
      ->run();

    $this->say("<info>Example PHPUnit files were copied to your application.</info>");
  }

}
