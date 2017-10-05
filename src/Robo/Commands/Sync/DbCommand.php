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
    foreach ($multisites as $multisite) {
      $this->say("Syncing db for site <comment>$multisite</comment>...");
      $result = $this->syncDbMultisite($multisite);
      if (!$result->wasSuccessful()) {
        $this->logger->error("Could not sync database for site '$multisite'.");
        throw new BltException("Could not sync database.");
      }
    }

    return $exit_code;
  }

  /**
   * Calls sync:db for a specific multisite.
   *
   * @param string $multisite_name
   *   The name of a multisite. E.g., if docroot/sites/example.com is the site,
   *   $multisite_name would be example.com.
   *
   * @return \Robo\Result
   */
  protected function syncDbMultisite($multisite_name) {
    $this->getConfig()->setSiteConfig($multisite_name);
    $result = $this->syncDbDefault();

    return $result;
  }

  /**
   * Copies remote db to local db for default site.
   *
   * @command sync:db
   *
   * @executeInDrupalVm
   */
  public function syncDbDefault() {
    $this->invokeCommand('setup:settings');

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
      ->option('create-db')
      ->assume(TRUE);

    if ($this->getConfigValue('drush.sanitize')) {
      $drush_version = $this->getInspector()->getDrushMajorVersion();
      if ($drush_version == 8) {
        $task->option('sanitize');
      }
      else {
        $task->drush('sql-sanitize');
      }
    }

    $task->drush('cache-clear drush');

    $result = $task->run();

    return $result;
  }

}
