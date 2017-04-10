<?php

namespace Acquia\Blt\Robo\Commands\Drupal;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Common\RandomString;

/**
 * Defines commands in the "drupal:*" namespace.
 */
class DrupalCommand extends BltTasks {

  /**
   * @command drupal:install
   */
  public function install() {
    $this->hashSalt();
    $task = $this->taskExec('drush site-install')
      ->dir($this->getConfigValue('docroot'))
      ->arg($this->getConfigValue('project.profile.name'))
      ->rawArg("install_configure_form.update_status_module='array(FALSE,FALSE)'")
      ->option('site-name', $this->getConfigValue('project.human_name'), '=')
      ->option('site-mail', $this->getConfigValue('drupal.account.mail'), '=')
      ->option('account-name', $this->getConfigValue('drupal.account.name'), '=')
      ->option('account-pass', $this->getConfigValue('drupal.account.pass'), '=')
      ->option('account-mail', $this->getConfigValue('drupal.account.mail'), '=')
      ->option('locale', $this->getConfigValue('drupal.locale'), '=')
      ->option('yes');

    if (!$this->getConfigValue('cm.strategy') == 'features') {
      $cm_core_key = $this->getConfigValue('cm.core.key');
      $task->option('config-dir', $this->getConfigValue("cm.core.dirs.$cm_core_key.path"));
    }

    $task->interactive()->run();
  }

  /**
   *
   */
  protected function hashSalt() {
    $hash_salt_file = $this->getConfigValue('repo.root') . '/salt.txt';
    if (!file_exists($hash_salt_file)) {
      $this->say("Writing hash salt to $hash_salt_file");
      $this->taskWriteToFile($hash_salt_file)
        ->line(RandomString::string(55))
        ->run();
    }
  }

}
