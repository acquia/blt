<?php

namespace Acquia\Blt\Robo\Commands\Doctor;

/**
 *
 */
class AcsfCheck extends DoctorCheck {

  public function performAllChecks() {
    $this->checkAcsfConfig();
  }

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
