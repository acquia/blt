<?php

namespace Acquia\Blt\Robo\Commands\Doctor;

use Acquia\Blt\Robo\Common\Executor;
use Acquia\Blt\Robo\Inspector\Inspector;
use Robo\Config\Config;

/**
 *
 */
class ComposerCheck extends DoctorCheck {

  /**
   * @var array
   */
  protected $composerJson;

  /**
   * @var array
   */
  protected $composerLock;

  /**
   * DoctorCheck constructor.
   */
  public function __construct(Config $config, Inspector $inspector, Executor $executor, $drush_status) {
    parent::__construct($config, $inspector, $executor, $drush_status);
    $this->setComposerJson();
    $this->setComposerLock();
  }

  /**
   * Sets $this->composerJson using root composer.json file.
   *
   * @return array
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
   * @return array
   */
  public function getComposerJson() {
    return $this->composerJson;
  }

  /**
   * Sets $this->composerJson using root composer.lock file.
   *
   * @return array
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
   * @return array
   */
  public function getComposerLock() {
    return $this->composerLock;
  }

  /**
   * Checks that composer.json is configured correctly.
   */
  public function performAllChecks() {
    $this->checkRequire();
    if (!$this->getInspector()->isVmCli()) {
      $this->checkPrestissimo();
    }

    return $this->problems;
  }

  protected function checkRequire() {
    if (!empty($this->composerJson['require-dev']['acquia/blt'])) {
      $this->logProblem('require', [
        "acquia/blt is defined as a development dependency in composer.json",
        "  Move acquia/blt out of the require-dev object and into the require object in composer.json.",
        "  This is necessary for BLT settings files to be available at runtime in production.",
      ], 'error');
    }
  }

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

  // @todo Check extras config.

}
