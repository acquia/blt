<?php

namespace Acquia\Blt\Robo\Commands\Sync;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Exceptions\BltException;

/**
 * Defines commands in the "sync:refresh*" namespace.
 */
class RefreshCommand extends BltTasks {

  /**
   * Copies remote db to local db, re-imports config, and executes db updates
   * for each multisite.
   *
   * @command sync:refresh:all
   * @executeInDrupalVm
   */
  public function refreshAll() {
    $exit_code = 0;
    $multisites = $this->getConfigValue('multisites');
    foreach ($multisites as $multisite) {
      $this->say("Refreshing site <comment>$multisite</comment>...");
      $exit_code = $this->refreshMultisite($multisite);
      if ($exit_code) {
        throw new BltException("Could not refresh site '$multisite'.");
      }
    }

    return $exit_code;
  }

  /**
   * Executes sync:refresh for a specific multisite.
   *
   * @param string $multisite_name
   *   The name of a multisite. E.g., if docroot/sites/example.com is the site,
   *   $multisite_name would be example.com.
   *
   * @return int
   */
  protected function refreshMultisite($multisite_name) {
    $this->getConfig()->setSiteConfig($multisite_name);
    return $this->refreshDefault();
  }

  /**
   * Copies remote db to local db, re-imports config, and executes db updates.
   *
   * @command sync:refresh
   * @executeInDrupalVm
   */
  public function refreshDefault() {
    $this->invokeCommands([
      'sync',
      'setup:update',
    ]);
  }

}
