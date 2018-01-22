<?php

namespace Acquia\Blt\Robo\Commands\Doctor;

/**
 *
 */
class ConfigCheck extends DoctorCheck {

  public function performAllChecks() {
    $this->checkGitConfig();
    $this->checkDeprecatedKeys();
  }

  /**
   * Check that general CI configuration is set correctly.
   */
  protected function checkGitConfig() {
    if (empty($this->getConfigValue('git.remotes'))) {
      $this->logProblem(__FUNCTION__, [
        "Git repositories are not defined in blt.yml.",
        "  Add values for git.remotes to blt.yml to enabled automated deployment.",
      ], 'comment');
    }
  }

  /**
   * Checks that is configured correctly at a high level.
   */
  protected function checkDeprecatedKeys() {
    $deprecated_keys = [
      'project.hash_salt',
      'project.profile.contrib',
      'project.vendor',
      'project.description',
      'project.themes',
      'hosting',
    ];
    $deprecated_keys_exist = FALSE;
    $outcome = [];
    foreach ($deprecated_keys as $deprecated_key) {
      if ($this->getConfigValue($deprecated_key)) {
        $outcome[] = "The '$deprecated_key' key is deprecated. Please remove it from blt.yml.";
        $deprecated_keys_exist = TRUE;
      }
    }

    if ($deprecated_keys_exist) {
      $this->logProblem(__FUNCTION__ . ':keys', $outcome, 'comment');
    }
  }

}
