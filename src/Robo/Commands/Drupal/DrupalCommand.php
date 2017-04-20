<?php

namespace Acquia\Blt\Robo\Commands\Drupal;

use Acquia\Blt\Robo\BltTasks;

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
   */
  public function install() {

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

    $status_code = $task->interactive()->run();

    return $status_code;
  }

}
