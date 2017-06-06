<?php

namespace Acquia\Blt\Robo\Commands\Ci;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Exceptions\BltException;
use Robo\Contract\VerbosityThresholdInterface;

/**
 * Defines commands in the "ci:*" namespace.
 */
class CiCommand extends BltTasks {

  /**
   * Initializes default Acquia Pipelines configuration for this project.
   *
   * @command ci:pipelines:init
   */
  public function pipelinesInit() {
    $result = $this->taskFilesystemStack()
      ->copy($this->getConfigValue('blt.root') . '/scripts/pipelines/acquia-pipelines.yml', $this->getConfigValue('repo.root') . '/acquia-pipelines.yml', TRUE)
      ->stopOnFail()
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
      ->run();

    if (!$result->wasSuccessful()) {
      throw new BltException("Could not initialize Acquia Pipelines configuration.");
    }

    $this->say("<info>A pre-configured acquia-pipelines.yml file was copied to your repository root.</info>");
  }

  /**
   * Initializes default Travis CI configuration for this project.
   *
   * @command ci:travis:init
   */
  public function travisInit() {
    $result = $this->taskFilesystemStack()
      ->copy($this->getConfigValue('blt.root') . '/scripts/travis/.travis.yml', $this->getConfigValue('repo.root') . '/.travis.yml', TRUE)
      ->stopOnFail()
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
      ->run();

    if (!$result->wasSuccessful()) {
      throw new BltException("Could not initialize Travis CI configuration.");
    }

    $this->say("<info>A pre-configured .travis.yml file was copied to your repository root.</info>");
  }

}
