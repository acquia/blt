<?php

namespace Acquia\Blt\Robo\Commands\Setup;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Exceptions\BltException;

/**
 * Defines commands in the "setup:config*" namespace.
 */
class ConfigCommand extends BltTasks {

  /**
   * Update current database to reflect the state of the Drupal file system.
   *
   * @command setup:update
   */
  public function update() {
    $this->invokeCommands(['setup:config-import']);
  }

  /**
   * Imports configuration from the config directory according to cm.strategy.
   *
   * @command setup:config-import
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
        ->assume(TRUE)
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

      switch ($strategy) {
        case 'core-only':
          $this->importCoreOnly($task, $cm_core_key);
          break;

        case 'config-split':
          $this->importConfigSplit($task, $cm_core_key);
          break;

        case 'features':
          $this->importFeatures($task, $cm_core_key);
          break;
      }

      $task->drush("cache-rebuild");
      $result = $task->run();
      if (!$result->wasSuccessful()) {
        throw new BltException("Failed to import configuration!");
      }

      if ($this->getInspector()->getDrushMajorVersion() == 8) {
        $this->checkFeaturesOverrides();
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
  }

  /**
   * Import configuration using features module.
   *
   * @param \Acquia\Blt\Robo\Tasks\DrushTask $task
   * @param string $cm_core_key
   */
  protected function importFeatures($task, $cm_core_key) {
    $task->drush("config-import")->arg($cm_core_key)->option('partial');
    if ($this->getConfig()->has('cm.features.bundle')) {
      $task->drush("pm-enable")->arg('features');
      // Clear drush caches to register features drush commands.
      $task->drush("cc")->arg('drush');
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
  }

  /**
   * Checks whether core config is overridden.
   *
   * @param string $cm_core_key
   *
   * @throws \Acquia\Blt\Robo\Exceptions\BltException
   */
  protected function checkConfigOverrides($cm_core_key) {
    // Check for configuration overrides.
    if (!$this->getConfigValue('cm.allow-overrides')) {
      $this->say("Checking for config overrides...");
      $config_overrides = $this->taskDrush()
        ->assume(FALSE)
        ->drush("cex")
        ->arg($cm_core_key);
      if (!$config_overrides->run()->wasSuccessful()) {
        throw new BltException("Configuration in the database does not match configuration on disk. You must re-export configuration to capture the changes. This could also indicate a problem with the import process, such as changed field storage for a field with existing content. To permit configuration overrides, set cm.allow-overrides to true in blt/project.yml.");
      }
    }
  }

}
