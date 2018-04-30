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
    $sites = $this->getAcsfSites();
    $this->getConfig()->set('multisites', $sites);
    $this->invokeCommand('artifact:update:drupal:all-sites');
  }


  /**
   * Gets a list of ACSF sites using ACSF module include and env vars.
   *
   * This will only correctly return sites when called from within an ACSF
   * environment.
   *
   * @return array|bool
   * @throws \Acquia\Blt\Robo\Exceptions\BltException
   */
  protected function getAcsfSites() {
    // @see https://cgit.drupalcode.org/acsf/tree/acsf_init/lib/sites/g/sites.inc?h=8.x-2.x
    $acsf_include_file = $this->getConfigValue('docroot') . '/sites/g/sites.inc';
    if (!file_exists($acsf_include_file)) {
      throw new BltException("Unable to get list of ACSF sites. Required file $acsf_include_file does not exist.");
    }
    require $acsf_include_file;

    $sites = [];
    // Look for list of sites and loop over it.
    if (($map = gardens_site_data_load_file()) && isset($map['sites'])) {
      // Acquire sites info.
      $sites = array();
      foreach ($map['sites'] as $domain => $site_details) {
        if (!isset($sites[$site_details['name']])) {
          $sites[$site_details['name']] = $site_details;
        }
        $sites[$site_details['name']]['domains'][] = $domain;
      }
    }
    else {
      throw new BltException("Unable to get list of ACSF sites.");
    }
    return $sites;
  }

}
