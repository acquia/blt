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
  public function update() {
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

    $this->invokeCommands(['drupal:config:import', 'drupal:toggle:modules']);
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

    if ($strategy == 'none') {
      // Still clear caches to regenerate frontend assets and such.
      $result = $this->taskDrush()->drush("cache-rebuild")->run();
      return $result;
    }

    $cm_core_key = 'sync';
    $this->logConfig($this->getConfigValue('cm'), 'cm');
    $task = $this->taskDrush();

    $this->invokeHook('pre-config-import');

    // If using core-only or config-split strategies, first check to see if
    // required config is exported.
    if (in_array($strategy, ['core-only', 'config-split'])) {
      $core_config_file = $this->getConfigValue('docroot') . '/' . $this->getConfigValue("cm.core.dirs.$cm_core_key.path") . '/core.extension.yml';

      if (!file_exists($core_config_file)) {
        $this->logger->warning("BLT will NOT import configuration, $core_config_file was not found.");
        // This is not considered a failure.
        return 0;
      }
    }

    // If exported site UUID does not match site active site UUID, set active
    // to equal exported.
    // @see https://www.drupal.org/project/drupal/issues/1613424
    $exported_site_uuid = $this->getExportedSiteUuid($cm_core_key);
    if ($exported_site_uuid) {
      $task->drush("config:set system.site uuid $exported_site_uuid");
    }

    switch ($strategy) {
      case 'core-only':
        $this->importCoreOnly($task, $cm_core_key);
        break;

      case 'config-split':
        // Drush task explicitly to turn on config_split and check if it was
        // successfully enabled. Otherwise default to core-only.
        $check_task = $this->taskDrush();
        $check_task->drush("pm-enable")->arg('config_split');
        $result = $check_task->run();
        if (!$result->wasSuccessful()) {
          $this->logger->warning('Import strategy is config-split, but the config_split module does not exist. Falling back to core-only.');
          $this->importCoreOnly($task, $cm_core_key);
          break;
        }
        $this->importConfigSplit($task, $cm_core_key);
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
   * @param string $cm_core_key
   *   Cm core key.
   */
  protected function importCoreOnly($task, $cm_core_key) {
    $task->drush("config-import")->arg($cm_core_key);
  }

  /**
   * Import configuration using config_split module.
   *
   * @param mixed $task
   *   Drush task.
   * @param string $cm_core_key
   *   Cm core key.
   */
  protected function importConfigSplit($task, $cm_core_key) {
    $task->drush("config-import")->arg($cm_core_key);
    // Runs a second import to ensure splits are
    // both defined and imported.
    $task->drush("config-import")->arg($cm_core_key);
  }

  /**
   * Checks whether core config is overridden.
   *
   * @throws \Acquia\Blt\Robo\Exceptions\BltException
   */
  protected function checkConfigOverrides() {
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
   * @param string $cm_core_key
   *   Cm core key.
   *
   * @return null
   *   Mixed.
   */
  protected function getExportedSiteUuid($cm_core_key) {
    $site_config_file = $this->getConfigValue('docroot') . '/' . $this->getConfigValue("cm.core.dirs.$cm_core_key.path") . '/system.site.yml';
    if (file_exists($site_config_file)) {
      $site_config = Yaml::parseFile($site_config_file);
      $site_uuid = $site_config['uuid'];

      return $site_uuid;
    }

    return NULL;
  }

}
