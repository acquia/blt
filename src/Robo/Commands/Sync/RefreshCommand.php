<?php

namespace Acquia\Blt\Robo\Commands\Sync;

use Acquia\Blt\Robo\BltTasks;

/**
 * Defines commands in the "sync:refresh*" namespace.
 */
class RefreshCommand extends BltTasks {

  /**
   * Copies remote db to local db, re-imports config, and executes db updates.
   *
   * @command sync:refresh
   */
  public function refresh() {
    return $this->invokeCommands([
      'sync',
      'setup:update',
    ]);
  }

}
