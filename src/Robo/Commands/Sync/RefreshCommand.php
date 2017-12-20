<?php

namespace Acquia\Blt\Robo\Commands\Sync;

use Acquia\Blt\Robo\BltTasks;

/**
 * Defines commands in the "sync:refresh*" namespace.
 */
class RefreshCommand extends BltTasks {

  /**
   * Copies remote db to local db, re-imports config, and executes db updates
   * for each multisite.
   *
   * This command does not use @executeInDrupalVm because it would require
   * SSH forwarding.
   *
   * @command sync:refresh:all
   *
   * @see https://github.com/acquia/blt/issues/1875
   */
  public function refreshAll() {
    $multisites = $this->getConfigValue('multisites');
    foreach ($multisites as $multisite) {
      $this->say("Refreshing site <comment>$multisite</comment>...");
      $this->switchSiteContext($multisite);
      $this->refreshDefault();
    }
  }

  /**
   * Executes composer install, runs frontend command, copies remote db to
   * local db, re-imports config, and executes db updates.
   *
   * @command sync:refresh
   *
   * @aliases sync
   */
  public function refreshDefault() {
    $this->invokeCommands($this->getConfigValue('sync.commands'));
  }

}
