<?php

namespace Acquia\Blt\Robo\Commands\Setup;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Common\RandomString;
use Acquia\Blt\Robo\Exceptions\BltException;

/**
 * Defines commands in the "drupal:*" namespace.
 */
class DrupalCommand extends BltTasks {

  /**
   * Installs Drupal and imports configuration.
   *
   * @command internal:drupal:install
   *
   * @validateDrushConfig
   * @hidden
   *
   * @return \Robo\Result
   *   The `drush site-install` command result.
   * @throws BltException
   */
  public function install() {

    // Allows for installs to define custom user 0 name.
    if ($this->getConfigValue('drupal.account.name') !== NULL) {
      $username = $this->getConfigValue('drupal.account.name');
    }
    else {
      // Generate a random, valid username.
      // @see \Drupal\user\Plugin\Validation\Constraint\UserNameConstraintValidator
      $username = RandomString::string(10, FALSE,
        function ($string) {
          return !preg_match('/[^\x{80}-\x{F7} a-z0-9@+_.\'-]/i', $string);
        },
        'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890!#%^&*()_?/.,+=><'
      );
    }
    /** @var \Acquia\Blt\Robo\Tasks\DrushTask $task */
    $task = $this->taskDrush()
      ->drush("site-install")
      ->arg($this->getConfigValue('project.profile.name'))
      ->rawArg("install_configure_form.enable_update_status_module=NULL")
      ->option('sites-subdir', $this->getConfigValue('site'))
      ->option('site-name', $this->getConfigValue('project.human_name'))
      ->option('site-mail', $this->getConfigValue('drupal.account.mail'))
      ->option('account-name', $username, '=')
      ->option('account-mail', $this->getConfigValue('drupal.account.mail'))
      ->option('locale', $this->getConfigValue('drupal.locale'))
      ->verbose(TRUE)
      ->printOutput(TRUE);

    // Install site from existing config if supported.
    $strategy = $this->getConfigValue('cm.strategy');
    $cm_core_key = $this->getConfigValue('cm.core.key');
    $install_from_config = $this->getConfigValue('cm.core.install_from_config');
    if (in_array($strategy, ['core-only', 'config-split']) && $cm_core_key == 'sync' && $install_from_config) {
      $core_config_file = $this->getConfigValue('docroot') . '/' . $this->getConfigValue("cm.core.dirs.$cm_core_key.path") . '/core.extension.yml';
      if (file_exists($core_config_file)) {
        $task->option('existing-config');
      }
    }

    $result = $task->interactive($this->input()->isInteractive())->run();
    if (!$result->wasSuccessful()) {
      throw new BltException("Failed to install Drupal!");
    }

    return $result;
  }

}
