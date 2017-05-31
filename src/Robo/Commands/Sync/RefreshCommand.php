<?php

namespace Acquia\Blt\Robo\Commands\Sync;

use Acquia\Blt\Robo\BltTasks;

/**
 * Defines commands in the "sync:refresh*" namespace.
 */
class RefreshCommand extends BltTasks {

  /**
   * Refreshes local environment from upstream testing database.
   *
   * @command refresh
   */
  public function refresh() {
    return $this->invokeCommands([
      'sync',
      'setup:update',
    ]);
  }

}
