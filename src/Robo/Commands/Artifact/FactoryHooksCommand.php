<?php

namespace Acquia\Blt\Robo\Commands\Artifact;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Exceptions\BltException;

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
   *   The site name. E.g., site1.
   * @param string $target_env
   *   The cloud env. E.g., dev
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
    $this->taskDrush()
      ->drush("cc drush")
      ->run();
    $this->say("Running updates for environment: $target_env");
    $drush_alias = "$site.$target_env";
    $result = $this->taskDrush()
      ->drush("drush @$drush_alias acsf-tools-list")
      ->run();
    if (!$result->wasSuccessful()) {
      throw new BltException("Unable to get list of ACSF sites.");
    }
    $output = $result->getMessage();
    // @todo Populate. Update ACSF tools to drush 9.
    $sites = [];
    $this->getConfig()->set('multisites', $sites);
    $this->invokeCommand('artifact:update:drupal:all-sites');
  }

}
