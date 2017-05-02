<?php

namespace Acquia\Blt\Robo\Commands\Ci;

use Acquia\Blt\Robo\BltTasks;
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
      ->copy($this->getConfigValue('blt.root') . '/scripts/pipelines/acquia-pipelines.yml', $this->getConfigValue('repo.root') . '/acquia-pipelines.yml')
      ->stopOnFail()
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
      ->run();

    return $result;
  }

  /**
   * Initializes default Travis CI configuration for this project.
   *
   * @command ci:travis:init
   */
  public function travisInit() {
    $result = $this->taskFilesystemStack()
      ->copy($this->getConfigValue('blt.root') . '/scripts/travis/.travis.yml', $this->getConfigValue('repo.root') . '/.travis.yml')
      ->stopOnFail()
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
      ->run();

    return $result;
  }

}
