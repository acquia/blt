<?php

namespace Acquia\Blt\Robo\Commands\Artifact;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Common\EnvironmentDetector;
use Acquia\Blt\Robo\Common\RandomString;
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
   *   The site name, e.g., site1.
   * @param string $target_env
   *   The cloud env, e.g., dev.
   * @param string $source_branch
   *   The source branch, e.g., master.
   * @param string $deployed_tag
   *   The tag or branch to which the source was deployed, e.g., master or
   *   1.0.0.
   * @param string $repo_url
   *   The repo url, e.g., s1@svn-3.bjaspan.hosting.acquia.com:s1.git.
   * @param string $repo_type
   *   The repo type, e.g., git.
   *
   * @command artifact:ac-hooks:post-code-deploy
   *
   * @throws \Acquia\Blt\Robo\Exceptions\BltException
   */
  public function postCodeDeploy($site, $target_env, $source_branch, $deployed_tag, $repo_url, $repo_type) {
    if (!EnvironmentDetector::isAcsfEnv($site, $target_env)) {
      $this->postCodeUpdate($site, $target_env, $source_branch, $deployed_tag, $repo_url, $repo_type);
    }
  }

  /**
   * Execute updates against an artifact hosted in AC Cloud.
   *
   * This is intended to be called from post-code-update.sh cloud hook.
   *
   * @param string $site
   *   The site name, e.g., site1.
   * @param string $target_env
   *   The cloud env, e.g., dev.
   * @param string $source_branch
   *   The source branch, e.g., master.
   * @param string $deployed_tag
   *   The tag or branch to which the source was deployed, e.g., master or
   *   1.0.0.
   * @param string $repo_url
   *   The repo url, e.g., s1@svn-3.bjaspan.hosting.acquia.com:s1.git.
   * @param string $repo_type
   *   The repo type, e.g., git.
   *
   * @command artifact:ac-hooks:post-code-update
   *
   * @throws \Acquia\Blt\Robo\Exceptions\BltException
   */
  public function postCodeUpdate($site, $target_env, $source_branch, $deployed_tag, $repo_url, $repo_type) {
    if (!EnvironmentDetector::isAcsfEnv($site, $target_env)) {
      try {
        $this->updateCloudSites($target_env);
        $success = TRUE;
        $this->sendPostCodeUpdateNotifications($site, $target_env, $source_branch, $deployed_tag, $success);
      }
      catch (BltException $e) {
        $success = FALSE;
        $this->sendPostCodeUpdateNotifications($site, $target_env, $source_branch, $deployed_tag, $success);
        throw $e;
      }
    }
  }

  /**
   * Execute updates against copied database.
   *
   * This is intended to be called from post-db-copy.sh cloud hook.
   *
   * @param string $site
   *   The site name. E.g., site1.
   * @param string $target_env
   *   The cloud env. E.g., dev.
   * @param string $db_name
   *   The source database name.
   * @param string $source_env
   *   The source environment. E.g., dev.
   *
   * @command artifact:ac-hooks:post-db-copy
   *
   * @throws \Acquia\Blt\Robo\Exceptions\BltException
   */
  public function postDbCopy($site, $target_env, $db_name, $source_env) {
    // Only run updates for ODEs, where DBs are copied automatically after new
    // code has been deployed. In other environments, DBs are usually copied
    // manually prior to initiating a code deploy, so an update is redundant.
    if (EnvironmentDetector::isAhOdeEnv($target_env)) {
      $this->invokeCommand('artifact:update:drupal:all-sites');
    }
  }

  /**
   * Execute updates after files are copied.
   *
   * This is intended to be called from post-files-copy.sh cloud hook.
   *
   * @param string $site
   *   The site name. E.g., site1.
   * @param string $target_env
   *   The cloud env. E.g., dev.
   * @param string $source_env
   *   The source environment. E.g., dev.
   *
   * @command artifact:ac-hooks:post-files-copy
   */
  public function postFilesCopy($site, $target_env, $source_env) {
    // Do nothing for now. Allow extension of this call.
  }

  /**
   * Execute sql-sanitize against a database hosted in AC Cloud.
   *
   * This is intended to be called from db-scrub.sh cloud hook.
   *
   * @param string $site
   *   The site name, e.g., site1.
   * @param string $target_env
   *   The cloud env, e.g., dev.
   * @param string $db_name
   *   The name of the database.
   * @param string $source_env
   *   The source environment.
   *
   * @command artifact:ac-hooks:db-scrub
   *
   * @throws \Exception
   */
  public function dbScrub($site, $target_env, $db_name, $source_env) {
    if (!EnvironmentDetector::isAcsfEnv($site, $target_env)) {
      $password = RandomString::string(10, FALSE,
        function ($string) {
          return !preg_match('/[^\x{80}-\x{F7} a-z0-9@+_.\'-]/i', $string);
        },
        'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890!#%^&*()_?/.,+=><'
      );
      $this->say("Scrubbing database in $target_env");
      $result = $this->taskDrush()
        ->drush("sql-sanitize --sanitize-password=\"$password\" --yes")
        ->run();
      if (!$result->wasSuccessful()) {
        throw new BltException("Failed to sanitize database!");
      }
      $this->taskDrush()
        ->drush("cr")
        ->run();
    }
  }

  /**
   * Executes updates against all ACE sites in the target environment.
   *
   * @param string $target_env
   *   Target env.
   *
   * @throws \Acquia\Blt\Robo\Exceptions\BltException
   */
  public function updateCloudSites($target_env) {
    $this->say("Running updates for environment: $target_env");
    $this->invokeCommand('artifact:update:drupal:all-sites');
    $this->say("Finished updates for environment: $target_env");
  }

  /**
   * Sends updates to notification endpoints.
   *
   * @param string $site
   *   Site.
   * @param string $target_env
   *   Target Env.
   * @param string $source_branch
   *   Source branch.
   * @param string $deployed_tag
   *   Deployed tag.
   * @param string $success
   *   Success.
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
   * Notify slack.
   *
   * @param string $success
   *   Success.
   * @param string $message
   *   Message.
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
   *   Hook URL.
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
   * @param string $url
   *   URL.
   * @param mixed $payload
   *   Payload.
   */
  protected function sendSlackNotification($url, $payload) {
    $this->say("Sending slack notification...");
    $data = "payload=" . json_encode($payload);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_exec($ch);
    curl_close($ch);
  }

}
