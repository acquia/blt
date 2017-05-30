<?php

namespace Acquia\Blt\Robo\Commands\Setup;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Config\YamlConfigProcessor;
use Robo\Config\YamlConfigLoader;

/**
 * Defines commands in the "setup:sync*" namespace.
 */
class SyncCommand extends BltTasks {

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

  /**
   * Synchronize local environment from remote (remote --> local).
   *
   * @command sync
   */
  public function sync($options = [
    'sync-files' => FALSE,
  ]) {

    $commands = [
      'local:sync:db',
    ];

    // @todo Read sync.files config.
    if ($options['sync-files']) {
      $commands[] = 'local:sync:files';
    }

    return $this->invokeCommands($commands);

  }

  /**
   * Iteratively synchronizes local database from remote for each multisite.
   *
   * @command local:sync:db:all
   */
  public function syncDbAll() {
    $multisites = $this->getConfigValue('multisites');
    foreach ($multisites as $multisite) {
      $this->say("Syncing db for site <comment>$multisite</comment>...");
      $result = $this->syncDbMultisite($multisite);
      if (!$result->wasSuccessful()) {
        return $result;
      }
    }

    return $result;
  }

  /**
   * Calls local:sync:db for a specific multisite.
   *
   * @param string $multisite_name
   *   The name of a multisite. E.g., if docroot/sites/example.com is the site,
   *   $multisite_name would be example.com.
   *
   * @return \Robo\Result
   */
  protected function syncDbMultisite($multisite_name) {
    $this->config->set('site', $multisite_name);
    $this->config->set('drush.uri', $multisite_name);

    // After having set site, this should now return the multisite
    // specific config.
    $site_config_file = $this->getConfigValue('blt.config-files.multisite');

    // Load multisite-specific config.
    $loader = new YamlConfigLoader();
    $processor = new YamlConfigProcessor();
    $processor->add($this->getConfig()->export());
    $processor->extend($loader->load($site_config_file));
    $this->getConfig()->import($processor->export());

    $result = $this->syncDbDefault();

    return $result;
  }

  /**
   * Synchronize local database from remote (remote --> local).
   *
   * @command local:sync:db
   */
  public function syncDbDefault() {
    $this->invokeCommand('setup:settings');

    $local_alias = '@' . $this->getConfigValue('drush.aliases.local');
    $remote_alias = '@' . $this->getConfigValue('drush.aliases.remote');

    $task = $this->taskDrush()
      ->alias('')
      ->assume('')
      ->drush('cache-clear drush')
      ->drush('sql-drop')
      ->drush('sql-sync')
      ->arg($remote_alias)
      ->arg($local_alias)
      ->option('structure-tables-key', 'lightweight')
      ->option('create-db');

    if ($this->getConfigValue('drush.sanitize')) {
      $task->option('sanitize');
    }

    $task->drush('cache-clear drush');
    $task->drush("$local_alias cache-rebuild");

    $result = $task->run();

    return $result;
  }

  /**
   * Synchronize local files from remote (remote --> local).
   *
   * @command local:sync:files
   */
  public function syncFiles() {
    $local_alias = '@' . $this->getConfigValue('drush.aliases.local');
    $remote_alias = '@' . $this->getConfigValue('drush.aliases.remote');
    $site_dir = $this->getConfigValue('site');

    $task = $this->taskDrush()
      ->alias('')
      ->assume('')
      ->uri('')
      ->drush('rsync')
      ->arg($remote_alias . ':%files')
      ->arg($this->getConfigValue('docroot') . "/sites/$site_dir/files")
      ->option('exclude-paths', 'styles:css:js');

    $result = $task->run();

    return $result;
  }

}
