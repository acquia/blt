<?php

namespace Acquia\Blt\Robo\Commands\Artifact;

use Acquia\Blt\Robo\BltTasks;

/**
 * Defines commands in the "artifact:acsf-hooks" namespace.
 */
class FactoryHooksCommand extends BltTasks {

  /**
   * Execute updates against an artifact hosted in AC Cloud.
   *
   * This is intended to be called from post-code-deploy.sh cloud hook.
   *
   * @param string $site
   *   The site name, e.g., site1.
   * @param string $target_env
   *   The cloud env, e.g., dev
   *
   * @command artifact:acsf-hooks:db-update
   */
  public function dbUpdate($site, $target_env, $db_role, $domain) {
    $this->updateAcsfSites($site, $target_env);
  }

  /**
   * Executes updates against all ACSF sites in the target environment.
   *
   * @param $site
   * @param $target_env
   *
   * @throws \Acquia\Blt\Robo\Exceptions\BltException
   */
  public function updateAcsfSites($site, $target_env) {
    $this->say("Running updates for site <comment>$site</comment> in environment <comment>$target_env</comment>.");
    $this->switchSiteContext($site);
    $this->invokeCommand('artifact:update:drupal');
  }

}
