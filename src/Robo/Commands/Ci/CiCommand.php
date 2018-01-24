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
   * @command recipes:ci:pipelines:init
   *
   * @aliases rcpi ci:pipelines:init
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
   * @command recipes:ci:travis:init
   *
   * @aliases rcti ci:travis:init
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

  /**
   * Initializes default Probo CI configuration for this project.
   *
   * @command recipes:ci:probo:init
   *
   * @aliases rcpri ci:probo:init
   */
  public function proboInit() {
    $result = $this->taskFilesystemStack()
      ->copy($this->getConfigValue('blt.root') . '/scripts/probo/.probo.yml', $this->getConfigValue('repo.root') . '/.probo.yml', TRUE)
      ->stopOnFail()
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
      ->run();

    if (!$result->wasSuccessful()) {
      throw new BltException("Could not initialize Probo CI configuration.");
    }

    $this->say("<info>A pre-configured .probo.yml file was copied to your repository root.</info>");
  }

  /**
   * Initializes default GitLab Pipelines configuration for this project.
   *
   * @command recipes:ci:gitlab:init
   *
   * @aliases rcgi ci:gitlab:init
   */
  public function gitlabInit() {
    $result = $this->taskFilesystemStack()
      ->copy($this->getConfigValue('blt.root') . '/scripts/gitlab/gitlab-ci.yml', $this->getConfigValue('repo.root') . '/.gitlab-ci.yml', TRUE)
      ->stopOnFail()
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
      ->run();
    if (!$result->wasSuccessful()) {
      throw new BltException("Could not initialize the GitLab Pipelines configuration.");
    }
    $this->say("<info>A pre-configured .gitlab-ci.yml file was copied to your repository root.</info>");
    $this->logger->warning("GitLab support is experimental and may not support all BLT features.");
  }

}
