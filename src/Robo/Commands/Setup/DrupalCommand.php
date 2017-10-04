<?php

namespace Acquia\Blt\Robo\Commands\Setup;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Common\RandomString;

/**
 * Defines commands in the "drupal:*" namespace.
 */
class DrupalCommand extends BltTasks {

  /**
   * Installs Drupal and imports configuration.
   *
   * @command internal:drupal:install
   *
   * @validateMySqlAvailable
   *
   * @return \Robo\Result
   *   The `drush site-install` command result.
   */
  public function install() {

    // Generate a random, valid username.
    // @see \Drupal\user\Plugin\Validation\Constraint\UserNameConstraintValidator
    $username = RandomString::string(10, FALSE,
      function ($string) {
        return !preg_match('/[^\x{80}-\x{F7} a-z0-9@+_.\'-]/i', $string);
      },
      'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890!#%^&*()_?/.,+=><'
    );

    /** @var \Acquia\Blt\Robo\Tasks\DrushTask $task */
    $task = $this->taskDrush()
      ->drush("site-install")
      ->arg($this->getConfigValue('project.profile.name'))
      ->rawArg("install_configure_form.update_status_module='array(FALSE,FALSE)'")
      ->rawArg("install_configure_form.enable_update_status_module=NULL")
      ->option('site-name', $this->getConfigValue('project.human_name'))
      ->option('site-mail', $this->getConfigValue('drupal.account.mail'))
      ->option('account-name', $username, '=')
      ->option('account-mail', $this->getConfigValue('drupal.account.mail'))
      ->option('locale', $this->getConfigValue('drupal.locale'))
      ->verbose(TRUE)
      ->assume(TRUE)
      ->printOutput(TRUE);

    $config_strategy = $this->getConfigValue('cm.strategy');

    // --config-dir is not valid for Drush 9.
    if ($config_strategy != 'none' && $this->getInspector()->getDrushMajorVersion() == 8) {
      $cm_core_key = $this->getConfigValue('cm.core.key');
      $task->option('config-dir', $this->getConfigValue("cm.core.dirs.$cm_core_key.path"));
    }

    $result = $task->detectInteractive()->run();
    if ($result->wasSuccessful()) {
      $this->getConfig()->set('state.drupal.installed', TRUE);
    }

    return $result;
  }

}
