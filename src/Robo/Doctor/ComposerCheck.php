<?php

namespace Acquia\Blt\Robo\Doctor;

use Acquia\Blt\Robo\Common\Executor;
use Acquia\Blt\Robo\Inspector\Inspector;
use Robo\Config\Config;

/**
 * BLT Doctor checks.
 */
class ComposerCheck extends DoctorCheck {

  /**
   * Composer.json.
   *
   * @var array
   */
  protected $composerJson;

  /**
   * Composer.lock.
   *
   * @var array
   */
  protected $composerLock;

  /**
   * DoctorCheck constructor.
   *
   * @param \Robo\Config\Config $config
   *   Robo config.
   * @param \Acquia\Blt\Robo\Inspector\Inspector $inspector
   *   BLT inspector.
   * @param \Acquia\Blt\Robo\Common\Executor $executor
   *   BLT executor.
   * @param array $drush_status
   *   Drush status.
   */
  public function __construct(Config $config, Inspector $inspector, Executor $executor, array $drush_status) {
    parent::__construct($config, $inspector, $executor, $drush_status);
    $this->setComposerJson();
    $this->setComposerLock();
  }

  /**
   * Sets $this->composerJson using root composer.json file.
   *
   * @return array
   *   Array.
   */
  protected function setComposerJson() {
    if (file_exists($this->getConfigValue('repo.root') . '/composer.json')) {
      $composer_json = json_decode(file_get_contents($this->getConfigValue('repo.root') . '/composer.json'), TRUE);
      $this->composerJson = $composer_json;

      return $composer_json;
    }

    return [];
  }

  /**
   * Sets $this->composerJson using root composer.lock file.
   *
   * @return array
   *   Array.
   */
  protected function setComposerLock() {
    if (file_exists($this->getConfigValue('repo.root') . '/composer.lock')) {
      $composer_lock = json_decode(file_get_contents($this->getConfigValue('repo.root') . '/composer.lock'), TRUE);
      $this->composerLock = $composer_lock;

      return $composer_lock;
    }

    return [];
  }

  /**
   * Checks that composer.json is configured correctly.
   */
  public function performAllChecks() {
    $this->checkRequire();
    $this->checkVersion();

    return $this->problems;
  }

  /**
   * Check require.
   */
  protected function checkRequire() {
    if (!empty($this->composerJson['require-dev']['acquia/blt'])) {
      $this->logProblem('require', [
        "acquia/blt is defined as a development dependency in composer.json",
        "  Move acquia/blt out of the require-dev object and into the require object in composer.json.",
        "  This is necessary for BLT settings files to be available at runtime in production.",
      ], 'error');
    }
  }

  /**
   * Check Composer version.
   */
  protected function checkVersion() {
    if (!$this->getInspector()->isComposerMinimumVersionSatisfied('2')) {
      $this->logProblem(__FUNCTION__, [
        "Composer 1 detected.",
        "  BLT requires Composer 2 to operate correctly. Composer 1 is end of life, and Composer 2 includes significant performance improvements. Upgrade to Composer 2 as soon as possible.",
      ], 'comment');
    }
  }

}
