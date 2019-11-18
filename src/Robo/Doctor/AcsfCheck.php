<?php

namespace Acquia\Blt\Robo\Doctor;

/**
 * BLT Doctor checks for ACSF.
 */
class AcsfCheck extends DoctorCheck {

  /**
   * Perform all checks.
   */
  public function performAllChecks() {
    $this->checkAcsfConfig();
  }

  /**
   * Check ACSF config.
   */
  protected function checkAcsfConfig() {
    $file_path = $this->getConfigValue('repo.root') . '/factory-hooks/pre-settings-php/includes.php';
    if (file_exists($file_path)) {
      $file_contents = file_get_contents($file_path);
      if (!strstr($file_contents, '/../vendor/acquia/blt/settings/blt.settings.php')) {
        $this->logProblem(__FUNCTION__, [
          "BLT settings are not included in your pre-settings-php include.",
          "  Add a require statement for \"/../vendor/acquia/blt/settings/blt.settings.php\" to $file_path",
        ], 'error');
      }
    }
  }

}
