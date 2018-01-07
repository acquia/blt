<?php

namespace Acquia\Blt\Robo\Commands\Doctor;

/**
 *
 */
class PhpCheck extends DoctorCheck {

  public function performAllChecks() {
    $this->checkPhpDateTimezone();
  }

  /**
   * Checks the php date.timezone setting is correctly set.
   */
  protected function checkPhpDateTimezone() {
    $dateTimezone = ini_get('date.timezone');
    $php_ini_file = php_ini_loaded_file();
    if (!$dateTimezone) {
      $this->logProblem(__FUNCTION__, [
        "PHP setting for date.timezone is not set.",
        "  Define date.timezone in $php_ini_file",
      ], 'error');
    }
  }

}
