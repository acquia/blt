<?php

namespace Acquia\Blt\Robo\Commands\Setup;

use Acquia\Blt\Robo\BltTasks;
use Robo\Contract\VerbosityThresholdInterface;
use Symfony\Component\Finder\Finder;

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
   * Import configuration from the config directory.
   *
   * @command setup:config-import
   */
  public function import() {
    $strategy = $this->getConfigValue('cm.strategy');
    $cm_core_key = $this->getConfigValue('cm.core.key');

    if ($strategy != 'none') {
      $this->invokeHook('pre-config-import');
      $drush_alias = $this->getConfigValue('drush.alias');

      $task = $this->taskExecStack()
        // Sometimes drush forgets where to find its aliases.
        ->exec("drush cc drush --yes")
        ->exec("drush @$drush_alias pm-enable config --yes")
        // Rebuild caches in case service definitions have changed.
        // @see https://www.drupal.org/node/2826466
        ->exec("drush @$drush_alias cache-rebuild")
        // Execute db updates.
        // This must happen before features are imported or configuration is imported.
        // For instance, if you add a dependency on a new extension to an
        // existing configuration file, you must enable that extension via an
        // update hook before attempting to import the configuration.
        // If a db update relies on updated configuration, you should import the
        //necessary configuration file(s) as part of the db update.
        ->exec("drush @$drush_alias updb --yes");

      switch ($strategy) {
        case 'core-only':
          if (file_exists($this->getConfigValue("cm.core.dirs.$cm_core_key.path") . '/core.extension.yml')) {
            $task->exec("drush @$drush_alias config-import $cm_core_key --yes");
          }
          break;

        case 'config-split':
          // We cannot use ${cm.core.dirs.${cm.core.key}.path} here because
          // cm.core.key may be 'vcs', which does not have a path defined in
          // BLT config. Perhaps this should be refactored.
          $core_config_file = $this->getConfigValue('docroot') . '/' . $this->getConfigValue('cm.core.dirs.sync.path') . '/core.extension.yml';
          if (file_exists($core_config_file)) {
            $task->exec("drush @$drush_alias pm-enable config_split --yes");
            $task->exec("drush @$drush_alias config-import sync --yes");
          }
          break;

        case 'features':
          $task->exec("drush @$drush_alias config-import $cm_core_key --partial --yes");
          if ($this->getConfig()->has('cm.features.bundle"')) {
            $task->exec("drush @$drush_alias pm-enable features --yes");
            // Clear drush caches to register features drush commands.
            $task->exec("drush cc drush --yes");
            foreach ($this->getConfigValue('cm.features.bundle') as $bundle) {
              $task->exec("drush @$drush_alias features-revert-all --bundle=$bundle --yes");
              // Revert all features again!
              // @see https://www.drupal.org/node/2851532
              $task->exec("drush @$drush_alias features-revert-all --bundle=$bundle --yes");
            }
          }
          if ($this->getConfigValue('cm.features.no-overrides')) {
            $this->say("Checking for features overrides...");
            foreach ($this->getConfigValue('cm.features.bundle') as $bundle) {
              $features_overriden = $task->exec("drush fl --bundle=${bundle} | grep -Ei '(changed|conflicts|added)( *)$");
              // @todo emit:
              // A feature in the ${bundle} bundle is overridden. You must re-export this feature to incorporate the changes.
            }
          }
          break;
      }
      $task->exec("drush @$drush_alias cache-rebuild");
      $task->run();

      $this->invokeHook('post-config-import');

    }
  }

}
