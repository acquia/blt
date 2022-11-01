<?php

namespace Acquia\Blt\Robo\Commands\Drupal;

use Acquia\Blt\Robo\Blt;
use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Common\UserConfig;
use Acquia\Blt\Robo\Exceptions\BltException;
use Symfony\Component\Yaml\Yaml;
use Zumba\Amplitude\Amplitude;

/**
 * Defines commands in the "setup:config*" namespace.
 */
class ConfigCommand extends BltTasks {

  /**
   * Update current database to reflect the state of the Drupal file system.
   *
   * @command drupal:update
   * @aliases du setup:update
   *
   * @throws \Robo\Exception\TaskException
   * @throws \Acquia\Blt\Robo\Exceptions\BltException
   */
  public function update(): void {
    $task = $this->taskDrush()
      ->stopOnFail()
      // Execute db updates.
      // This must happen before configuration is imported. For instance, if you
      // add a dependency on a new extension to an existing configuration file,
      // you must enable that extension via an update hook before attempting to
      // import the configuration. If a db update relies on updated
      // configuration, you should import the necessary configuration file(s) as
      // part of the db update.
      ->drush("updb");

    $result = $task->run();
    if (!$result->wasSuccessful()) {
      throw new BltException("Failed to execute database updates!");
    }

    $this->invokeCommand('drupal:config:import');
    $this->invokeCommand('drupal:deploy:hook');
  }

  /**
   * Imports configuration from the config directory according to cm.strategy.
   *
   * @command drupal:config:import
   * @aliases dci setup:config-import
   *
   * @validateDrushConfig
   *
   * @throws \Robo\Exception\TaskException
   * @throws \Exception
   */
  public function import() {
    $strategy = $this->getConfigValue('cm.strategy');

    $userConfig = new UserConfig(Blt::configDir());
    $eventInfo = $userConfig->getTelemetryUserData();
    $eventInfo['strategy'] = $strategy;
    Amplitude::getInstance()->queueEvent('config import', $eventInfo);

    if ($strategy === 'none') {
      // Still clear caches to regenerate frontend assets and such.
      return $this->taskDrush()->drush("cache-rebuild")->run();
    }

    $this->logConfig($this->getConfigValue('cm'), 'cm');
    $task = $this->taskDrush();

    $this->invokeHook('pre-config-import');

    // If using core-only or config-split strategies, first check to see if
    // required config is exported.
    if (in_array($strategy, ['core-only', 'config-split'])) {
      $core_config_file = $this->getConfigValue('docroot') . '/' . $this->getConfigValue("cm.core.dirs.sync.path") . '/core.extension.yml';

      if (!file_exists($core_config_file)) {
        $this->logger->warning("BLT will NOT import configuration, $core_config_file was not found.");
        // This is not considered a failure.
        return 0;
      }
    }

    // If exported site UUID does not match site active site UUID, set active
    // to equal exported.
    // @see https://www.drupal.org/project/drupal/issues/1613424
    $exported_site_uuid = $this->getExportedSiteUuid();
    if ($exported_site_uuid) {
      $task->drush("config:set system.site uuid $exported_site_uuid");
    }

    switch ($strategy) {
      case 'core-only':
        $this->importCoreOnly($task);
        break;

      case 'config-split':
        // Drush task explicitly to turn on config_split and check if it was
        // successfully enabled. Otherwise default to core-only.
        $check_task = $this->taskDrush();
        $check_task->drush("pm-enable")->arg('config_split');
        $result = $check_task->run();
        if (!$result->wasSuccessful()) {
          $this->logger->warning('Import strategy is config-split, but the config_split module does not exist. Falling back to core-only.');
          $this->importCoreOnly($task);
          break;
        }
        $this->importConfigSplit($task);
        break;
    }

    $task->drush("cache-rebuild");
    $result = $task->run();
    if (!$result->wasSuccessful()) {
      throw new BltException("Failed to import configuration!");
    }

    $this->checkConfigOverrides();

    $result = $this->invokeHook('post-config-import');

    return $result;
  }

  /**
   * Import configuration using core config management only.
   *
   * @param mixed $task
   *   Drush task.
   */
  protected function importCoreOnly($task): void {
    $task->drush("config-import");
  }

  /**
   * Import configuration using config_split module.
   *
   * @param mixed $task
   *   Drush task.
   */
  protected function importConfigSplit($task): void {
    $task->drush("config-import");
    // Runs a second import to ensure splits are
    // both defined and imported.
    $task->drush("config-import");
  }

  /**
   * Checks whether core config is overridden.
   *
   * @throws \Acquia\Blt\Robo\Exceptions\BltException
   * @throws \Robo\Exception\TaskException
   */
  protected function checkConfigOverrides(): void {
    if (!$this->getConfigValue('cm.allow-overrides') && !$this->getInspector()->isActiveConfigIdentical()) {
      $task = $this->taskDrush()
        ->stopOnFail()
        ->drush("config-status");
      $result = $task->run();
      if (!$result->wasSuccessful()) {
        throw new BltException("Unable to determine configuration status.");
      }
      throw new BltException("Configuration in the database does not match configuration on disk. This indicates that your configuration on disk needs attention. Please read https://support.acquia.com/hc/en-us/articles/360034687394--Configuration-in-the-database-does-not-match-configuration-on-disk-when-using-BLT");
    }
  }

  /**
   * Returns the site UUID stored in exported configuration.
   *
   * @return null
   *   Mixed.
   */
  protected function getExportedSiteUuid() {
    $site_config_file = $this->getConfigValue('docroot') . '/' . $this->getConfigValue("cm.core.dirs.sync.path") . '/system.site.yml';
    if (file_exists($site_config_file)) {
      $site_config = Yaml::parseFile($site_config_file);
      return $site_config['uuid'];
    }

    return NULL;
  }

  /**
   * Runs drush's deploy hook.
   *
   * @see https://www.drush.org/latest/commands/deploy_hook/
   *
   * @command drupal:deploy:hook
   *
   * @throws \Robo\Exception\TaskException
   * @throws \Acquia\Blt\Robo\Exceptions\BltException
   */
  public function deployHook(): void {
    $task = $this->taskDrush()
      ->stopOnFail()
      // Execute drush's deploy:hook. This runs "deploy" functions.
      // These are one-time functions that run AFTER config is imported.
      ->drush("deploy:hook");

    $result = $task->run();
    if (!$result->wasSuccessful()) {
      throw new BltException("Failed to run 'drush deploy:hook'!");
    }
  }

}
