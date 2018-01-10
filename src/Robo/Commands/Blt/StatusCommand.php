<?php

namespace Acquia\Blt\Robo\Commands\Blt;

use Acquia\Blt\Robo\BltTasks;

/**
 * Defines doctor command.
 */
class StatusCommand extends BltTasks {

  /**
   * Gets BLT status.
   *
   * @command status
   */
  public function status() {
    $status = $this->getInspector()->getStatus();
    $this->printArrayAsTable($status);
  }

}
