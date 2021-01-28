<?php

namespace Acquia\Blt\Robo\Commands\Drupal;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Exceptions\BltException;
use Robo\Exception\TaskException;

/**
 * Defines commands in the "sync" namespace.
 */
class SyncCommand extends BltTasks {

  /**
   * Synchronize each multisite.
   *
   * @command drupal:sync:all-sites
   * @aliases dsa sync:all
   */
  public function allSites() {
    $multisites = $this->getConfigValue('multisites');
    $sync_public_file  = $this->getConfigValue('sync.public-files');
    $sync_private_file = $this->getConfigValue('sync.private-files');
    $this->printSyncMap($multisites);
    $continue = $this->confirm("Continue?", TRUE);
    if (!$continue) {
      return 0;
    }
    foreach ($multisites as $multisite) {
      $this->say("Refreshing site <comment>$multisite</comment>...");
      $this->switchSiteContext($multisite);
      $this->sync([
        'sync-public-files' => $sync_public_file,
        'sync-private-files' => $sync_private_file,
      ]);
    }
  }

  /**
   * Synchronize local env from remote (remote --> local).
   *
   * Copies remote db to local db, re-imports config, and executes db updates
   * for each multisite.
   *
   * @param array $options
   *   Array of CLI options.
   *
   * @command drupal:sync:default:site
   * @aliases ds drupal:sync drupal:sync:default sync sync:refresh
   */
  public function sync(array $options = [
    'sync-public-files' => FALSE,
    'sync-private-files' => FALSE,
  ]) {
    $commands = $this->getConfigValue('sync.commands');
    if ($options['sync-public-files'] || $this->getConfigValue('sync.public-files')) {
      $commands[] = 'drupal:sync:public-files';
    }
    if ($options['sync-private-files'] || $this->getConfigValue('sync.private-files')) {
      $commands[] = 'drupal:sync:private-files';
    }
    $this->invokeCommands($commands);
  }

  /**
   * Copies public remote files to local machine.
   *
   * @command drupal:sync:public-files
   *
   * @aliases dsf sync:files drupal:sync:files
   *
   * @validateDrushConfig
   *
   * @todo Support multisite.
   */
  public function syncPublicFiles() {
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
   * Copies private remote files to local machine.
   *
   * @command drupal:sync:private-files
   *
   * @aliases dspf
   *
   * @validateDrushConfig
   */
  public function syncPrivateFiles() {
    $remote_alias = '@' . $this->getConfigValue('drush.aliases.remote');
    $site_dir = $this->getConfigValue('site');
    $private_files_local_path = $this->getConfigValue('repo.root') . "/files-private/$site_dir";

    $task = $this->taskDrush()
      ->alias('')
      ->uri('')
      ->drush('rsync')
      ->arg($remote_alias . ':%private/')
      ->arg($private_files_local_path)
      ->option('exclude-paths', implode(':', $this->getConfigValue('sync.exclude-paths')));

    $result = $task->run();

    return $result;
  }

  /**
   * Iteratively copies remote db to local db for each multisite.
   *
   * @command drupal:sync:db:all-sites
   * @aliases dsba sync:all:db
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
   *
   * @throws \Acquia\Blt\Robo\Exceptions\BltException
   */
  public function syncDb() {
    $local_alias = '@' . $this->getConfigValue('drush.aliases.local');
    $remote_alias = '@' . $this->getConfigValue('drush.aliases.remote');

    $task = $this->taskDrush()
      ->alias('')
      ->drush('cache-clear drush')
      ->drush('sql-sync')
      ->arg($remote_alias)
      ->arg($local_alias)
      ->option('--target-dump', sys_get_temp_dir() . '/tmp.target.sql.gz')
      ->option('structure-tables-key', 'lightweight')
      ->option('create-db');
    $task->drush('cr');

    if ($this->getConfigValue('drush.sanitize')) {
      $task->drush('sql-sanitize');
    }

    try {
      $result = $task->run();
    }
    catch (TaskException $e) {
      $this->say('Sync failed. Often this is due to Drush version mismatches: https://support.acquia.com/hc/en-us/articles/360035203713-Permission-denied-during-BLT-sync-or-drush-sql-sync');
      throw new BltException($e->getMessage());
    }

    return $result;
  }

  /**
   * Print sync map.
   *
   * @param array $multisites
   *   Array of multisites.
   */
  protected function printSyncMap(array $multisites) {
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
