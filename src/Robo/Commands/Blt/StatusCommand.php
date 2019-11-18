<?php

namespace Acquia\Blt\Robo\Commands\Blt;

use Acquia\Blt\Robo\BltTasks;

/**
 * Defines status command.
 */
class StatusCommand extends BltTasks {

  /**
   * Gets BLT status.
   *
   * @command blt:status
   *
   * @aliases status
   */
  public function status() {
    $status = $this->getInspector()->getStatus();
    $this->printArrayAsTable($status);
  }

}
