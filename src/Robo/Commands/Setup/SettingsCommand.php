<?php

namespace Acquia\Blt\Robo\Commands\Setup;

use Acquia\Blt\Robo\BltTasks;
use Robo\Contract\VerbosityThresholdInterface;

/**
 * Defines commands in the "setup:settings" namespace.
 */
class SettingsCommand extends BltTasks {

  /**
   * @command setup:settings
   */
  public function generateDefaultSettingsFiles() {
    $this->taskFilesystemStack()
      ->copy($this->getConfigValue('repo.root') . '/blt/example.project.local.yml', $this->getConfigValue('repo.root') . '/blt/project.local.yml')
      ->stopOnFail()
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
      ->run();
  }

}
