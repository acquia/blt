<?php

namespace Acquia\Blt\Robo\Commands\Sync;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Config\YamlConfigProcessor;
use Robo\Config\YamlConfigLoader;

/**
 * Defines commands in the "setup:db*" namespace.
 */
class DbCommand extends BltTasks {

  /**
   * Iteratively copies remote db to local db for each multisite.
   *
   * @command sync:db:all
   */
  public function syncDbAll() {
    $exit_code = 0;
    $multisites = $this->getConfigValue('multisites');
    foreach ($multisites as $multisite) {
      $this->say("Syncing db for site <comment>$multisite</comment>...");
      $result = $this->syncDbMultisite($multisite);
      if (!$result->wasSuccessful()) {
        return $result->getExitCode();
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
   * Copies remote db to local db for default site.
   *
   * @command sync:db
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

}
