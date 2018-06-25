<?php

namespace Acquia\Blt\Robo\Commands\Setup;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Exceptions\BltException;
use Symfony\Component\Yaml\Yaml;

/**
 * Defines commands in the "setup:config*" namespace.
 */
class ConfigCommand extends BltTasks {

  /**
   * Update current database to reflect the state of the Drupal file system.
   *
   * @command drupal:update
   * @aliases du setup:update
   * @executeInVm
   */
  public function update() {
    $this->invokeCommands(['drupal:config:import', 'drupal:toggle:modules']);
  }

  /**
   * Imports configuration from the config directory according to cm.strategy.
   *
   * @command drupal:config:import
   * @aliases dci setup:config-import
   *
   * @validateDrushConfig
   * @executeInVm
   */
  public function import() {
    $strategy = $this->getConfigValue('cm.strategy');
    $cm_core_key = $this->getConfigValue('cm.core.key');
    $this->logConfig($this->getConfigValue('cm'), 'cm');

    if ($strategy != 'none') {
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

      $task = $this->taskDrush()
        ->stopOnFail()
        // Sometimes drush forgets where to find its aliases.
        ->drush("cc")->arg('drush')
        // Rebuild caches in case service definitions have changed.
        // @see https://www.drupal.org/node/2826466
        ->drush("cache-rebuild")
        // Execute db updates.
        // This must happen before features are imported or configuration is
        // imported. For instance, if you add a dependency on a new extension to
        // an existing configuration file, you must enable that extension via an
        // update hook before attempting to import the configuration.
        // If a db update relies on updated configuration, you should import the
        // necessary configuration file(s) as part of the db update.
        ->drush("updb");

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
          $this->importConfigSplit($task, $cm_core_key);
          break;

        case 'features':
          $this->importFeatures($task, $cm_core_key);

          if ($this->getConfigValue('cm.features.no-overrides')) {
            // @codingStandardsIgnoreLine
            $this->checkFeaturesOverrides();
          }
          break;
      }

      $task->drush("cache-rebuild");
      $result = $task->run();
      if (!$result->wasSuccessful()) {
        throw new BltException("Failed to import configuration!");
      }

      $this->checkConfigOverrides($cm_core_key);

      $result = $this->invokeHook('post-config-import');

      return $result;
    }
  }

  /**
   * Import configuration using core config management only.
   *
   * @param \Acquia\Blt\Robo\Tasks\DrushTask $task
   * @param string $cm_core_key
   */
  protected function importCoreOnly($task, $cm_core_key) {
    $task->drush("config-import")->arg($cm_core_key);
  }

  /**
   * Import configuration using config_split module.
   *
   * @param \Acquia\Blt\Robo\Tasks\DrushTask $task
   * @param string $cm_core_key
   */
  protected function importConfigSplit($task, $cm_core_key) {
    $task->drush("pm-enable")->arg('config_split');
    $task->drush("config-import")->arg($cm_core_key);
    // Runs a second import to ensure splits are
    // both defined and imported.
    $task->drush("config-import")->arg($cm_core_key);
  }

  /**
   * Import configuration using features module.
   *
   * @param \Acquia\Blt\Robo\Tasks\DrushTask $task
   * @param string $cm_core_key
   */
  protected function importFeatures($task, $cm_core_key) {
    $task->drush("config-import")->arg($cm_core_key)->option('partial');
    $task->drush("pm-enable")->arg('features');
    $task->drush("cc")->arg('drush');
    if ($this->getConfig()->has('cm.features.bundle')) {
      // Clear drush caches to register features drush commands.
      foreach ($this->getConfigValue('cm.features.bundle') as $bundle) {
        $task->drush("features-import-all")->option('bundle', $bundle);
        // Revert all features again!
        // @see https://www.drupal.org/node/2851532
        $task->drush("features-import-all")->option('bundle', $bundle);
      }
    }
  }

  /**
   * Checks whether features are overridden.
   *
   * @throws \Exception
   *   If cm.features.no-overrides is true, and there are features overrides
   *   an exception will be thrown.
   */
  protected function checkFeaturesOverrides() {
    if ($this->getConfigValue('cm.features.no-overrides')) {
      // @codingStandardsIgnoreStart
      $this->say("Checking for features overrides...");
      if ($this->getConfig()->has('cm.features.bundle')) {
        $task = $this->taskDrush()->stopOnFail();
        foreach ($this->getConfigValue('cm.features.bundle') as $bundle) {
          $task->drush("fl")
            ->option('bundle', $bundle)
            ->option('format', 'json');
          $result = $task->printOutput(TRUE)->run();

          if (!$result->wasSuccessful()) {
            throw new BltException("Unable to determine if features in bundle $bundle are overridden.");
          }

          $output = $result->getMessage();
          $features_overridden = preg_match('/(changed|conflicts|added)( *)$/', $output);
          if ($features_overridden) {
            throw new BltException("A feature in the $bundle bundle is overridden. You must re-export this feature to incorporate the changes.");
          }
        }
      }
    }
    // @codingStandardsIgnoreEnd
  }

  /**
   * Checks whether core config is overridden.
   *
   * @param string $cm_core_key
   *
   * @throws \Acquia\Blt\Robo\Exceptions\BltException
   */
  protected function checkConfigOverrides($cm_core_key) {
    if (!$this->getConfigValue('cm.allow-overrides') && !$this->getInspector()->isActiveConfigIdentical()) {
      throw new BltException("Configuration in the database does not match configuration on disk. BLT has attempted to automatically fix this by re-exporting configuration to disk. Please read https://github.com/acquia/blt/wiki/Configuration-override-test-and-errors");
    }
  }

  /**
   * Returns the site UUID stored in exported configuration.
   *
   * @param string $cm_core_key
   *
   * @return null
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
