<?php

namespace Acquia\Blt\Robo\Commands\Tests;

use Acquia\Blt\Robo\BltTasks;
use Robo\Contract\VerbosityThresholdInterface;

/**
 * Defines commands in the "tests" namespace.
 */
class TestsCommandBase extends BltTasks {

  /**
   * Creates the reports directory, if it does not exist.
   */
  protected function createReportsDir() {
    // Create reports dir.
    $logs_dir = $this->getConfigValue('reports.localDir');
    $this->taskFilesystemStack()
      ->mkdir($logs_dir)
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
      ->run();
  }

}
