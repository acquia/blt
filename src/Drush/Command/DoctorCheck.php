<?php

namespace Acquia\Blt\Drush\Command;

abstract class DoctorCheck {

  /**
   * DoctorCheck constructor.
   */
  public function __construct(BltDoctor $doctor) {
    $this->doctor = $doctor;
  }

}
