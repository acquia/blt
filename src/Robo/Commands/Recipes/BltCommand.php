<?php

namespace Acquia\Blt\Robo\Commands\Recipes;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Exceptions\BltException;
use Robo\Contract\VerbosityThresholdInterface;

/**
 * Defines commands in the "recipes:blt:*" namespace.
 */
class BltCommand extends BltTasks {

  /**
   * Generates example files for writing custom commands and hooks.
   *
   * @command recipes:blt:command:init
   *
   * @aliases rbci rbic examples:init
   *
   * @throws \Acquia\Blt\Robo\Exceptions\BltException
   */
  public function commandInit() {
    $result = $this->taskFilesystemStack()
      ->copy(
        $this->getConfigValue('blt.root') . '/scripts/blt/examples/Commands/ExampleCommands.php',
        $this->getConfigValue('repo.root') . '/blt/src/Blt/Plugin/Commands/ExampleCommands.php', FALSE)
      ->stopOnFail()
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
      ->run();

    if (!$result->wasSuccessful()) {
      throw new BltException("Could not copy example files into the repository root.");
    }

    $this->say("<info>Example commands and hooks were copied into your application. You must modify composer.json to autoload them via PSR-4, as shown here: https://github.com/acquia/blt-project/blob/11.2.0/composer.json#L93</info>");
  }

  /**
   * Generates example files for writing custom filesets.
   *
   * @command recipes:blt:filesystem:init
   *
   * @aliases rbfi
   *
   * @throws \Acquia\Blt\Robo\Exceptions\BltException
   */
  public function filesetInit() {
    $result = $this->taskFilesystemStack()
      ->copy(
        $this->getConfigValue('blt.root') . '/scripts/blt/examples/Filesets/ExampleFilesets.php',
        $this->getConfigValue('repo.root') . '/blt/src/Blt/Plugin/Filesets/ExampleFilesets.php', FALSE)
      ->stopOnFail()
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
      ->run();

    if (!$result->wasSuccessful()) {
      throw new BltException("Could not copy example files into the repository root.");
    }

    $this->say("<info>Example filesets were copied into your application. You must modify composer.json to autoload them via PSR-4, as shown here: https://github.com/acquia/blt-project/blob/11.2.0/composer.json#L93</info>");
  }

}
