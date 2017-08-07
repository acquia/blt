<?php

namespace Acquia\Blt\Robo\Commands\Sync;

use Acquia\Blt\Robo\BltTasks;

/**
 * Defines commands in the "sync" namespace.
 */
class SyncCommand extends BltTasks {

  /**
   * Synchronize local env from remote (remote --> local).
   *
   * @command sync
   */
  public function sync($options = [
    'sync-files' => FALSE,
  ]) {

    $commands = [
      'sync:db',
    ];

    if ($options['sync-files'] || $this->getConfigValue('sync.files')) {
      $commands[] = 'sync:files';
    }

    $this->invokeCommands($commands);

  }

}
