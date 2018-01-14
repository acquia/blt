<?php

namespace Acquia\Blt\Robo\Commands\Sync;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Exceptions\BltException;

/**
 * Defines commands in the "sync" namespace.
 */
class SyncCommand extends BltTasks {

  /**
   * Synchronize each multisite.
   *
   * This command does not use @executeInDrupalVm because it would require
   * SSH forwarding.
   *
   * @command sync:all
   *
   * @see https://github.com/acquia/blt/issues/1875
   */
  public function all() {
    $multisites = $this->getConfigValue('multisites');
    $this->printSyncMap($multisites);
    $continue = $this->confirm("Continue?");
    if (!$continue) {
      return 0;
    }
    foreach ($multisites as $multisite) {
      $this->say("Refreshing site <comment>$multisite</comment>...");
      $this->switchSiteContext($multisite);
      $this->sync();
    }
  }

  /**
   * Synchronize local env from remote (remote --> local).
   *
   * Copies remote db to local db, re-imports config, and executes db updates
   * for each multisite.
   *
   * @command sync
   */
  public function sync($options = [
    'sync-files' => FALSE,
  ]) {

    $commands = $this->getConfigValue('sync.commands');
    if ($options['sync-files'] || $this->getConfigValue('sync.files')) {
      $commands[] = 'sync:files';
    }
    $this->invokeCommands($commands);
  }

  /**
   * Copies remote files to local machine.
   *
   * @command sync:files
   *
   * @validateDrushConfig
   *
   * @todo Support multisite.
   */
  public function syncFiles() {
    $local_alias = '@' . $this->getConfigValue('drush.aliases.local');
    $remote_alias = '@' . $this->getConfigValue('drush.aliases.remote');
    $site_dir = $this->getConfigValue('site');

    $task = $this->taskDrush()
      ->alias('')
      ->uri('')
      ->drush('rsync')
      ->arg($remote_alias . ':%files/')
      ->arg($this->getConfigValue('docroot') . "/sites/$site_dir/files")
      ->option('exclude-paths', implode(':', $this->getConfigValue('sync.exclude-paths')));

    $result = $task->run();

    return $result;
  }

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

    $this->printSyncMap($multisites);
    $continue = $this->confirm("Continue?");
    if (!$continue) {
      return $exit_code;
    }

    foreach ($multisites as $multisite) {
      $this->say("Refreshing site <comment>$multisite</comment>...");
      $this->switchSiteContext($multisite);
      $result = $this->syncDb();
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
  public function syncDb() {
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

  /**
   * @param $multisites
   *
   * @return mixed
   */
  protected function printSyncMap($multisites) {
    $this->say("Sync operations be performed for the following drush aliases:");
    $sync_map = [];
    foreach ($multisites as $multisite) {
      $this->switchSiteContext($multisite);
      $sync_map[$multisite]['local'] = '@' . $this->getConfigValue('drush.aliases.local');
      $sync_map[$multisite]['remote'] = '@' . $this->getConfigValue('drush.aliases.remote');
      $this->say($sync_map[$multisite]['remote'] . " => " . $sync_map[$multisite]['local']);
    }
    $this->say("To modify the set of aliases for syncing, set the values for drush.aliases.local and drush.aliases.remote in docroot/sites/[site]/blt.yml");
  }

}
