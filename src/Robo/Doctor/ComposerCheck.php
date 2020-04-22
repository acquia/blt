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
   */
  public function __construct(Config $config, Inspector $inspector, Executor $executor, string $drush_status) {
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
    $this->checkPrestissimo();
    $this->checkDrupalCore();

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
   * Check prestissimo.
   */
  protected function checkPrestissimo() {
    $prestissimo_intalled = $this->getExecutor()->execute("composer global show | grep hirak/prestissimo")->run()->wasSuccessful();
    if (!$prestissimo_intalled) {
      $this->logProblem(__FUNCTION__ . ":plugins", [
        "hirak/prestissimo plugin for composer is not installed.",
        "  Run <comment>composer global require hirak/prestissimo</comment> to install it.",
        "  This will improve composer install/update performance by parallelizing the download of dependency information.",
      ], 'comment');
    }
  }

  /**
   * Check Drupal core.
   */
  protected function checkDrupalCore() {
    if (empty($this->composerJson['require']['drupal/core']) && empty($this->composerJson['require']['drupal/core-recommended'])) {
      $this->logProblem(__FUNCTION__ . ":plugins", [
        "drupal/core or drupal/core-recommended are not required by the root composer.json.",
        "  This impairs performance by preventing zaporylie/composer-drupal-optimizations from taking effect.",
      ], 'comment');
    }
  }

}
