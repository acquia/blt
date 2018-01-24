<?php

namespace Acquia\Blt\Robo\Commands\Blt;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Exceptions\BltException;
use Robo\Contract\VerbosityThresholdInterface;

/**
 * Defines commands in the "examples:*" namespace.
 */
class ExamplesCommand extends BltTasks {

  /**
   * Generates example files for writing custom commands and hooks.
   *
   * @command recipes:blt:init:command
   *
   * @aliases rbic examples:init
   */
  public function init() {
    $result = $this->taskFilesystemStack()
      ->copy(
        $this->getConfigValue('blt.root') . '/scripts/blt/examples/Commands/ExampleCommand.php',
        $this->getConfigValue('repo.root') . '/blt/src/Commands/ExampleCommand.php', FALSE)
      ->copy(
        $this->getConfigValue('blt.root') . '/scripts/blt/examples/Hooks/ExampleHook.php',
        $this->getConfigValue('repo.root') . '/blt/src/Hooks/ExampleHook.php', FALSE)
      ->copy(
        $this->getConfigValue('blt.root') . '/scripts/blt/examples/Test/ExampleTest.php',
        $this->getConfigValue('repo.root') . '/tests/phpunit/ExampleTest.php', FALSE)
      ->copy(
        $this->getConfigValue('blt.root') . '/scripts/blt/examples/Test/Examples.feature',
        $this->getConfigValue('repo.root') . '/tests/behat/features/Examples.feature', FALSE)
      ->copy(
        $this->getConfigValue('blt.root') . '/scripts/blt/examples/Filesets.php',
        $this->getConfigValue('repo.root') . '/blt/src/Filesets.php', FALSE)
      ->stopOnFail()
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
      ->run();

    if (!$result->wasSuccessful()) {
      throw new BltException("Could not copy example files into the repository root.");
    }

    $this->say("<info>Example commands and hooks were copied to your repository root.</info>");
  }

}
