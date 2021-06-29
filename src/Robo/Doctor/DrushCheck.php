<?php

namespace Acquia\Blt\Robo\Doctor;

use function file_exists;

/**
 * BLT Doctor checks.
 */
class DrushCheck extends DoctorCheck {

  /**
   * Perform all checks.
   */
  public function performAllChecks() {
    // @todo Implement performAllChecks() method.
  }

  /**
   * Checks for local.drush.yml file and prints messaging to screen.
   */
  protected function checkLocalDrushFile() {
    $drush_site_yml = $this->getConfigValue('docroot') . "/sites/default/local.drush.yml";
    if (!file_exists($drush_site_yml)) {
      $this->logProblem(__FUNCTION__, [
        "Local drushrc file does not exist.",
        "Create $drush_site_yml.",
        "Run `blt setup:drush:settings` to generate it automatically, or run `blt setup` to run the entire setup process.",
      ], 'error');
    }
  }

  /**
   * Check drush aliases.
   */
  protected function checkDrushAliases() {
    $result = $this->getExecutor()->drush('site:alias --format=json')->silent(TRUE)->run();
    if (!$result->wasSuccessful()) {
      $this->logProblem(__FUNCTION__, [
        "Cannot find any valid drush aliases.",
      ], 'error');
    }
  }

}
