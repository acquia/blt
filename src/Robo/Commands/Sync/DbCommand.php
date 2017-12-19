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
      $result = $this->taskExecStack()
        ->dir($this->getConfigValue('repo.root'))
        // @todo Pass all $arg['v'].
        ->exec($this->getConfigValue('repo.root') . "/vendor/bin/blt sync:db --define site=$multisite")
        ->run();
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
      ->option('create-db')
      ->uri($this->getConfigValue('drush.uri'));

    if ($this->getConfigValue('drush.sanitize')) {
      $task->drush('sql-sanitize');
    }

    $task->drush('cache-clear drush');

    $result = $task->run();

    return $result;
  }

}
