<?php

namespace Acquia\Blt\Robo\Commands\Doctor;

/**
 *
 */
class DrupalCheck extends DoctorCheck {

  public function performAllChecks() {
    $this->checkCoreExists();
    $this->checkDrupalBootstrapped();
    $this->checkDrupalInstalled();
  }

  /**
   * Indicates whether Drupal core files exist in the docroot.
   *
   * @return bool
   */
  protected function coreExists() {
    return file_exists($this->drushStatus['root'] . '/core/includes/install.core.inc');
  }

  /**
   * Checks that Drupal core files exist in the docroot.
   */
  protected function checkCoreExists() {
    if (!$this->coreExists()) {
      $this->logProblem(__FUNCTION__, [
        "Drupal core is missing!",
        "",
        "  Looked for docroot in {$this->drushStatus['root']}.",
        "Check and re-install your composer dependencies.",
      ], 'error');
    }
  }

  /**
   * Checks that drush is able to bootstrap Drupal Core.
   *
   * This is only possible if Drupal is installed.
   */
  protected function checkDrupalBootstrapped() {
    if (empty($this->drushStatus['bootstrap']) || $this->drushStatus['bootstrap'] != 'Successful') {
      $this->logProblem(__FUNCTION__, [
        'Could not bootstrap Drupal via drush without alias.',
      ], 'comment');
    }
  }

  /**
   * Checks that Drupal is installed.
   */
  protected function checkDrupalInstalled() {
    if (!$this->getInspector()->isDrupalInstalled()) {
      $this->logProblem(__FUNCTION__, [
        "Drupal is not installed.",
        "",
        'Run `blt drupal:install` to install Drupal locally.',
      ], 'error');
    }
  }

}
