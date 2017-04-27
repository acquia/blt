<?php

namespace Acquia\Blt\Robo\Commands\Drupal;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Common\RandomString;

/**
 * Defines commands in the "drupal:*" namespace.
 */
class DrupalCommand extends BltTasks {

  /**
   * Installs Drupal.
   *
   * @command drupal:install
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
      }
    );

    $drush_alias = $this->getConfigValue('drush.alias');
    $task = $this->taskExec("drush @$drush_alias site-install")
      ->detectInteractive()
      ->printOutput(TRUE)
      ->dir($this->getConfigValue('docroot'))
      ->arg($this->getConfigValue('project.profile.name'))
      ->rawArg("install_configure_form.update_status_module='array(FALSE,FALSE)'")
      ->option('site-name', $this->getConfigValue('project.human_name'), '=')
      ->option('site-mail', $this->getConfigValue('drupal.account.mail'), '=')
      ->option('account-name', $username, '=')
      ->option('account-mail', $this->getConfigValue('drupal.account.mail'), '=')
      ->option('locale', $this->getConfigValue('drupal.locale'), '=')
      ->option('yes');

    if (!$this->getConfigValue('cm.strategy') == 'features') {
      $cm_core_key = $this->getConfigValue('cm.core.key');
      $task->option('config-dir', $this->getConfigValue("cm.core.dirs.$cm_core_key.path"));
    }

    $result = $task->interactive()->run();
    if ($result->wasSuccessful()) {
      $this->getConfig()->set('state.drupal.installed', TRUE);
    }

    return $result;
  }

}
