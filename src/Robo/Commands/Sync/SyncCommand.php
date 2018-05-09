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
   * @command drupal:sync:all-sites
   * @aliases dsa sync:all
   * @executeInVm
   */
  public function allSites() {
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
   * @command drupal:sync:default:site
   * @aliases ds drupal:sync drupal:sync:default sync sync:refresh
   * @executeInVm
   */
  public function sync($options = [
    'sync-files' => FALSE,
  ]) {

    $commands = $this->getConfigValue('sync.commands');
    if ($options['sync-files'] || $this->getConfigValue('sync.files')) {
      $commands[] = 'drupal:sync:files';
    }
    $this->invokeCommands($commands);
  }

  /**
   * Copies remote files to local machine.
   *
   * @command drupal:sync:files
   *
   * @aliases dsf sync:files
   *
   * @validateDrushConfig
   * @executeInVm
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
   * @command drupal:sync:db:all-sites
   * @aliases dsba sync:all:db
   * @executeInVm
   */
  public function syncDbAllSites() {
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
   * @command drupal:sync:default:db
   *
   * @aliases dsb drupal:sync:db sync:db
   * @validateDrushConfig
   * @executeInVm
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
      ->option('--target-dump', sys_get_temp_dir() . '/tmp.target.sql.gz')
      ->option('structure-tables-key', 'lightweight')
      ->option('create-db');

    if ($this->getConfigValue('drush.sanitize')) {
      $task->drush('sql-sanitize');
    }

    $task->drush('cr');
    $task->drush('sqlq "TRUNCATE cache_entity"');

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
      $this->say("  * <comment>" . $sync_map[$multisite]['remote'] . "</comment> => <comment>" . $sync_map[$multisite]['local'] . "</comment>");
    }
    $this->say("To modify the set of aliases for syncing, set the values for drush.aliases.local and drush.aliases.remote in docroot/sites/[site]/blt.yml");
  }

}
