<?php

namespace Acquia\Blt\Robo\Commands\Tests;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Exceptions\BltException;

/**
 * Defines commands in the "tests:acsf:*" namespace.
 */
class AcsfCommand extends BltTasks {

  /**
   * Executes the acsf-init-validate command.
   *
   * @command tests:acsf:validate
   */
  public function validateAcsf() {
    $this->say("Validating ACSF settings...");
    $task = $this->taskDrush()
      ->stopOnFail()
      ->drush("--include=modules/contrib/acsf/acsf_init acsf-init-verify");
    $result = $task->run();
    if (!$result->wasSuccessful()) {
      throw new BltException("Failed to verify ACSF settings. Re-run acsf-init and commit the results.");
    }
  }

}
