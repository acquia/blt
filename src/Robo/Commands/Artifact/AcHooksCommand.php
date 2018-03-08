<?php

namespace Acquia\Blt\Robo\Commands\Artifact;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Exceptions\BltException;

/**
 * Defines commands in the "artifact:ac-hooks" namespace.
 */
class AcHooksCommand extends BltTasks {

  /**
   * Execute updates against an artifact hosted in AC Cloud.
   *
   * This is intended to be called from post-code-deploy.sh cloud hook.
   *
   * @param string $site
   *   The site name. E.g., site1.
   * @param string $target_env
   *   The cloud env. E.g., dev
   * @param string $source_branch
   *   The source branch. E.g., master.
   * @param string $deployed_tag
   *   The tag or branch to which the source was deployed. E.g., master or
   * 1.0.0.
   * @param string $repo_url
   *   The repo url. E.g., s1@svn-3.bjaspan.hosting.acquia.com:s1.git
   * @param string $repo_type
   *   The repo type. E.g., git.
   *
   * @command artifact:ac-hooks:post-code-deploy
   */
  public function postCodeDeploy($site, $target_env, $source_branch, $deployed_tag, $repo_url, $repo_type) {
    $this->postCodeUpdate($site, $target_env, $source_branch, $deployed_tag, $repo_url, $repo_type);
  }

  /**
   * Execute updates against an artifact hosted in AC Cloud.
   *
   * This is intended to be called from post-code-update.sh cloud hook.
   *
   * @param string $site
   *   The site name. E.g., site1.
   * @param string $target_env
   *   The cloud env. E.g., dev
   * @param string $source_branch
   *   The source branch. E.g., master.
   * @param string $deployed_tag
   *   The tag or branch to which the source was deployed. E.g., master or
   * 1.0.0.
   * @param string $repo_url
   *   The repo url. E.g., s1@svn-3.bjaspan.hosting.acquia.com:s1.git
   * @param string $repo_type
   *   The repo type. E.g., git.
   *
   * @command artifact:ac-hooks:post-code-update
   *
   * @throws \Exception
   */
  public function postCodeUpdate($site, $target_env, $source_branch, $deployed_tag, $repo_url, $repo_type) {
    try {
      $this->updateSites($site, $target_env);
      $success = TRUE;
      $this->sendPostCodeUpdateNotifications($site, $target_env, $source_branch, $deployed_tag, $success);
    }
    catch (\Exception $e) {
      $success = FALSE;
      $this->sendPostCodeUpdateNotifications($site, $target_env, $source_branch, $deployed_tag, $success);
      throw $e;
    }
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

  /**
   * Reinstalls Drupal in an ODE.
   */
  public function updateOdeSites() {
    $this->invokeCommand('artifact:install:drupal');
  }

  /**
   * Executes updates against all ACE sites in the target environment.
   *
   * @param $target_env
   */
  public function updateAceSites($target_env) {
    $this->say("Running updates for environment: $target_env");
    $this->invokeCommand('artifact:update:drupal:all-sites');
    $this->say("Finished updates for environment: $target_env");
  }

  /**
   * Sends updates to notification endpoints.
   *
   * @param $site
   * @param $target_env
   * @param $source_branch
   * @param $deployed_tag
   * @param $success
   */
  protected function sendPostCodeUpdateNotifications($site, $target_env, $source_branch, $deployed_tag, $success) {
    $is_tag = $source_branch != $deployed_tag;

    if ($success) {
      if ($is_tag) {
        $message = "An updated deployment has been made to *$site.$target_env* using tag *$deployed_tag*.";
      }
      else {
        $message = "An updated deployment has been made to *$site.$target_env* using branch *$source_branch* as *$deployed_tag*.";
      }
    }
    else {
      $message = "Deployment has FAILED for environment *$site.$target_env*.";
    }

    $this->notifySlack($success, $message);
  }

  /**
   * @param $success
   * @param $message
   */
  protected function notifySlack($success, $message) {
    $slack_webhook_url = $this->getSlackWebhookUrl();
    if ($slack_webhook_url) {
      $payload = [
        'username' => 'Acquia Cloud',
        'text' => $message,
        'icon_emoji' => $success ? ':mostly_sunny:' : ':rain_cloud:',
      ];
      $this->sendSlackNotification($slack_webhook_url, $payload);
    }
  }

  /**
   * Gets slack web url.
   *
   * @return array|false|mixed|null|string
   */
  protected function getSlackWebhookUrl() {
    if ($this->getConfig()->has('slack.webhook-url')) {
      return $this->getConfigValue('slack.webhook-url');
    }
    elseif (getenv('SLACK_WEBHOOK_URL')) {
      return getenv('SLACK_WEBHOOK_URL');
    }

    $this->say("Slack webhook url not found. To enable Slack notifications, set <comment>slack.webhook-url</comment>.");
    return FALSE;
  }

  /**
   * Sends a message to a slack channel.
   *
   * @param $url
   * @param $payload
   */
  protected function sendSlackNotification($url, $payload) {
    $this->say("Sending slack notification...");
    $data = "payload=" . json_encode($payload);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    $result = curl_exec($ch);
    curl_close($ch);
  }

  /**
   * Executes updates against all sites.
   *
   * @param $site
   * @param $target_env
   */
  protected function updateSites($site, $target_env) {
    if (preg_match('/01(dev|test)/', $target_env)) {
      $this->updateAcsfSites($site, $target_env);
    }
    elseif (preg_match('/01devup|01testup|01update|01live/', $target_env)) {
      // Do not run deploy updates on 01live in case a branch is deployed in
      // prod.
      return;
    }
    elseif (preg_match('/ode[[:digit:]]/', $target_env)) {
      $this->updateOdeSites();
    }
    else {
      $this->updateAceSites($target_env);
    }
  }

}
