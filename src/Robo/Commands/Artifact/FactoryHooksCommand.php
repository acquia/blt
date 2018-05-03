<?php

namespace Acquia\Blt\Robo\Commands\Artifact;

use Acquia\Blt\Robo\BltTasks;

/**
 * Defines commands in the "artifact:acsf-hooks" namespace.
 */
class FactoryHooksCommand extends BltTasks {

  /**
   * Execute updates against an artifact hosted in ACSF.
   *
   * This is intended to be called via the db-update factory hook whenever
   * code is deployed from the management console and the user has opted to
   * apply DB updates.
   *
   * Note that ACSF calls this hook once per site being updated.
   *
   * @param string $site
   *   The "ACSF site" name (actually subscription name). E.g., foo.
   * @param string $target_env
   *   The cloud env. E.g., 01dev
   * @param string $db_role
   *   Internal to ACSF.
   * @param string $domain
   *   Domain for the site being updated. E.g., foo1.foo.acsitefactory.com.
   *
   * @command artifact:acsf-hooks:db-update
   */
  public function dbUpdate($site, $target_env, $db_role, $domain) {
    $this->updateAcsfSites($domain, $target_env);
  }

  /**
   * Executes updates against a single ACSF site in the target environment.
   *
   * @param $domain
   * @param $target_env
   *
   * @throws \Acquia\Blt\Robo\Exceptions\BltException
   */
  public function updateAcsfSites($domain, $target_env) {
    $this->taskDrush()
      ->drush("cc drush")
      ->run();
    $this->say("Running updates for site <comment>$domain</comment> in environment <comment>$target_env</comment>.");
    $this->invokeCommand('artifact:update:drupal');
  }

}
