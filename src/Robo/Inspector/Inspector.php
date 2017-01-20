<?php

namespace Acquia\Blt\Robo\Inspector;

use Acquia\Blt\Robo\Common\Executor;
use Acquia\Blt\Robo\Common\IO;
use Acquia\Blt\Robo\Config\ConfigAwareTrait;
use Acquia\Blt\Robo\Tasks\BltTasks;
use Robo\Common\BuilderAwareTrait;
use Robo\Contract\BuilderAwareInterface;
use Robo\Contract\ConfigAwareInterface;

/**
 * Class Inspector.
 *
 * @package Acquia\Blt\Robo\Common
 */
class Inspector implements BuilderAwareInterface, ConfigAwareInterface {

  use BuilderAwareTrait;
  use ConfigAwareTrait;
  use IO;

  /** @var Executor */
  protected $executor;

  /**
   * Inspector constructor.
   *
   * @param \Acquia\Blt\Robo\Common\Executor $executor
   */
  public function __construct(Executor $executor) {
    $this->executor = $executor;
  }

  /**
   * @return bool
   */
  public function isRepoRootPresent() {
    return file_exists($this->getConfigValue('repo.root'));
  }

  /**
   * @return bool
   */
  public function isDocrootPresent() {
    return file_exists($this->getConfigValue('docroot'));
  }

  /**
   * @return bool
   */
  public function isDrupalSettingsFilePresent() {
    return file_exists($this->getConfigValue('drupal.settings_file'));
  }

  /**
   * @return bool
   */
  public function isDrupalSettingsFileValid() {
    $settings_file_contents = file_get_contents($this->getConfigValue('drupal.settings_file'));
    if (!strstr($settings_file_contents,
      '/../vendor/acquia/blt/settings/blt.settings.php')
    ) {
      return FALSE;
    }

    return TRUE;
  }

  /**
   * Checks that Drupal is installed.
   */
  public function isDrupalInstalled() {
    // This will only run once per command. If Drupal is installed mid-command,
    // this value needs to be changed.
    if (!$this->getConfigValue('state.drupal.installed')) {
      $installed = $this->getDrupalInstalled();
      $this->setStateDrupalInstalled($installed);

      return $installed;
    }

    return $this->getConfigValue('state.drupal.installed');
  }

  /**
   * @return bool
   */
  protected function getDrupalInstalled() {
    $result = $this->executor->drush("sqlq \"SHOW TABLES LIKE 'config'\"");
    $output = trim($result->getOutputData());
    $installed = $result->wasSuccessful() && $output == 'config';

    return $installed;
  }

  /**
   * @param $installed
   *
   * @return $this
   */
  protected function setStateDrupalInstalled($installed) {
    $this->getConfig()->set('state.drupal.installed', $installed);

    return $this;
  }

  /**
   * Checks if a given command exists on the system.
   *
   * @param $command string the command binary only. E.g., "drush" or "php".
   *
   * @return bool
   *   TRUE if the command exists, otherwise FALSE.
   */
  public static function commandExists($command) {
    exec("command -v $command >/dev/null 2>&1", $output, $exit_code);
    return $exit_code == 0;
  }

  /**
   *
   */
  public function isBehatConfigured() {
    return file_exists($this->getConfigValue('repo.root') . '/tests/behat/local.yml');
  }

  /**
   *
   */
  public function setDrushStatus() {
    if (!$this->getConfigValue('state.drush.status')) {
      $drush_status = json_decode($this->execDrush("status --format=json"),
        TRUE);
      $this->getConfig()->set('state.drush.status', $drush_status);
    }

    return $this;
  }

  public function isPhantomJsConfigured() {
    return $this->isPhantomJsRequired() && $this->isPhantomJsScriptConfigured() && $this->isPhantomJsBinaryPresent();
  }

  public function isPhantomJsRequired() {
    $result = $this->executor->executeCommand("grep 'jakoch/phantomjs-installer' composer.json");
    return $result->wasSuccessful();
  }

  public function isPhantomJsScriptConfigured() {
    $result = $this->executor->executeCommand("grep installPhantomJS composer.json");

    return $result->wasSuccessful();
  }

  public function isPhantomJsBinaryPresent() {
    return file_exists("{$this->getConfigValue('composer.bin')}/phantomjs");
  }
}
