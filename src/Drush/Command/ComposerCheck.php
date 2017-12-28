<?php

namespace Acquia\Blt\Drush\Command;

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
  public function __construct(BltDoctor $doctor) {
    parent::__construct($doctor);
    $this->setComposerJson();
    $this->setComposerLock();
  }

  /**
   * Sets $this->composerJson using root composer.json file.
   *
   * @return array
   */
  protected function setComposerJson() {

    if (file_exists($this->doctor->getRepoRoot() . '/composer.json')) {
      $composer_json = json_decode(file_get_contents($this->doctor->getRepoRoot() . '/composer.json'), TRUE);
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
    if (file_exists($this->doctor->getRepoRoot() . '/composer.lock')) {
      $composer_lock = json_decode(file_get_contents($this->doctor->getRepoRoot() . '/composer.lock'), TRUE);
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
  public function checkComposer() {
    if (!empty($this->composerJson['require-dev']['acquia/blt'])) {
      $this->doctor->logOutcome(__FUNCTION__ . ':require', [
        "acquia/blt is defined as a development dependency in composer.json",
        "  Move acquia/blt out of the require-dev object and into the require object in composer.json.",
        "  This is necessary for BLT settings files to be available at runtime in production.",
      ], 'error');
    }
    else {
      $this->doctor->logOutcome(__FUNCTION__ . ':require', [
        "acquia/blt is in composer.json's require object.",
      ], 'info');
    }

    if ('vagrant' != $_SERVER['USER']) {
      $prestissimo_intalled = drush_shell_exec("composer global show | grep hirak/prestissimo");
      if (!$prestissimo_intalled) {
        $this->doctor->logOutcome(__FUNCTION__ . ":plugins", [
          "hirak/prestissimo plugin for composer is not installed.",
          "  Run `composer global require hirak/prestissimo:^0.3` to install it.",
          "  This will improve composer install/update performance by parallelizing the download of dependency information.",
        ], 'comment');
      }
      else {
        $this->doctor->logOutcome(__FUNCTION__ . ':plugins', [
          "hirak/prestissimo plugin for composer is installed.",
        ], 'info');
      }
    }
    drush_shell_exec("composer --version");
    $composer_version = drush_shell_exec_output();
    $this->doctor->statusTable['composer-version'] = $composer_version;
  }

}
