<?php

namespace Acquia\Blt\Robo\Commands\Sync;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Exceptions\BltException;

/**
 * Defines commands in the "artifact:ac-hooks" namespace.
 */
class AcHooks extends BltTasks {

  /**
   *
   *
   * @command artifact:ac-hooks:post-code-update
   *
   * @see https://github.com/acquia/blt/issues/1875
   */
  public function postCodeUpdate($site, $target_env, $source_branch, $deployed_tag, $repo_url, $repo_type) {
    if (preg_match('/01(dev|test)/', $target_env)) {
      $this->updateAcsfSites($site, $target_env);
    }
    elseif (preg_match('/01devup|01testup|01update|01live/', $target_env)) {
      // Do not run deploy updates on 01live in case a branch is deployed in prod.
      return FALSE;
    }
    elseif (preg_match('/ode[[:digit:]]/', $target_env)) {
      $this->updateOde();
    }
    else {
      $this->updateAce($target_env);
    }
  }

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

  public function updateAce($target_env) {
    $this->say("Running updates for environment: $target_env");
    $this->invokeCommand('artifact:update:drupal:all-sites');
    $this->say("Finished updates for environment: $target_env");
  }

  public function updateOde() {
    $this->invokeCommand('artifact:install:drupal');
  }

  public function sendDeploymentUpdates($site, $target_env, $source_branch, $deployed_tag, $success) {
    $url = "";
    $is_tag = $source_branch != $deployed_tag;

    if ($success) {
      if ($is_tag) {
        $message = "An updated deployment has been made to *$site.$target_env* using tag *$deployed_tag*.";
      }
      else {
        $message = "An updated deployment has been made to *$site.$target_env* using branch *$source_branch* as *$deployed_tag*.";
      }

      $payload = [
        'username' => 'Acquia Cloud',
        'text' => $message,
        'icon_emoji' => ':mostly_sunny:',
      ];
    }
    else {
      $payload = [
        'username' => 'Acquia Cloud',
        'text' => "Deployment has FAILED for environment *$site.$target_env*.",
        'icon_emoji' => ':rain_cloud:',
      ];
    }

    $this->sendSlackUpdate($url, $payload);
  }

  protected function sendSlackUpdate($url, $payload) {
    $data = "payload=" . json_encode($payload);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    curl_close($ch);
  }

}
