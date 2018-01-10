<?php

namespace Acquia\Blt\Robo\Commands\Sync;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Exceptions\BltException;

/**
 * Defines commands in the "setup:db*" namespace.
 */
class DbCommand extends BltTasks {

  /**
   * Iteratively copies remote db to local db for each multisite.
   *
   * @command sync:db:all
   *
   * @executeInDrupalVm
   */
  public function syncDbAll() {
    $exit_code = 0;
    $multisites = $this->getConfigValue('multisites');

    $this->say("A sql-sync will be performed for the following drush aliases:");
    $sync_map = [];
    foreach ($multisites as $multisite) {
      $this->switchSiteContext($multisite);
      $sync_map[$multisite]['local'] = '@' . $this->getConfigValue('drush.aliases.local');
      $sync_map[$multisite]['remote'] = '@' . $this->getConfigValue('drush.aliases.remote');

      $this->say($sync_map[$multisite]['remote'] . " => " . $sync_map[$multisite]['local']);
    }
    $this->say("To modify the set of aliases for syncing, set the values for drush.aliases.local and drush.aliases.remote in docroot/sites/[site]/blt.site.yml");
    $continue = $this->confirm("Continue?");
    if (!$continue) {
      return $exit_code;
    }

    foreach ($multisites as $multisite) {
      $this->switchSiteContext($multisite);
      $result = $this->syncDbDefault();
      if (!$result->wasSuccessful()) {
        $this->logger->error("Could not sync database for site <comment>$multisite</comment>.");
        throw new BltException("Could not sync database.");
      }
    }

    return $exit_code;
  }

  /**
   * Copies remote db to local db for default site.
   *
   * @command sync:db
   *
   * @validateDrushConfig
   * @executeInDrupalVm
   */
  public function syncDbDefault() {
    $local_alias = '@' . $this->getConfigValue('drush.aliases.local');
    $remote_alias = '@' . $this->getConfigValue('drush.aliases.remote');

    $task = $this->taskDrush()
      ->alias('')
      ->drush('cache-clear drush')
      ->drush('sql-drop')
      ->drush('sql-sync')
      ->arg($remote_alias)
      ->arg($local_alias)
      ->option('structure-tables-key', 'lightweight')
      ->option('create-db');

    if ($this->getConfigValue('drush.sanitize')) {
      $task->drush('sql-sanitize');
    }

    $task->drush('cache-clear drush');

    $result = $task->run();

    return $result;
  }

}
