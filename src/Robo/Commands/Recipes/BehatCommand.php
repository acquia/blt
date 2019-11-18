<?php

namespace Acquia\Blt\Robo\Commands\Recipes;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Exceptions\BltException;
use Robo\Contract\VerbosityThresholdInterface;

/**
 * Defines commands in the "recipes:behat:*" namespace.
 */
class BehatCommand extends BltTasks {

  /**
   * Generates example files for writing custom Behat tests.
   *
   * @command recipes:behat:init
   *
   * @throws \Acquia\Blt\Robo\Exceptions\BltException
   */
  public function init() {
    $result = $this->taskFilesystemStack()
      ->copy(
        $this->getConfigValue('blt.root') . '/scripts/blt/examples/Test/Examples.feature',
        $this->getConfigValue('repo.root') . '/tests/behat/features/Examples.feature', FALSE)
      ->stopOnFail()
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
      ->run();

    if (!$result->wasSuccessful()) {
      throw new BltException("Could not copy example files into the repository root.");
    }

    $this->say("<info>Example Behat tests were copied into your application.</info>");
  }

}
