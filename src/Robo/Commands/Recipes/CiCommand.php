<?php

namespace Acquia\Blt\Robo\Commands\Recipes;

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
   * @command recipes:ci:pipelines:init
   *
   * @aliases rcpi ci:pipelines:init
   */
  public function pipelinesInit() {
    $result = $this->taskFilesystemStack()
      ->copy($this->getConfigValue('blt.root') . '/scripts/pipelines/acquia-pipelines.yaml', $this->getConfigValue('repo.root') . '/acquia-pipelines.yaml', TRUE)
      ->stopOnFail()
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
      ->run();

    if (!$result->wasSuccessful()) {
      throw new BltException("Could not initialize Acquia Pipelines configuration.");
    }

    $this->say("<info>A pre-configured acquia-pipelines.yaml file was copied to your repository root.</info>");
    $this->say("<info>To support copying DBs into newly-deployed CDEs, follow these instructions:</info>");
    $this->say("<info>https://docs.acquia.com/acquia-cloud/develop/pipelines/cli/install</info>");
  }

}
