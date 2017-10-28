<?php
namespace Drush\Commands;

use Drush\Commands\DrushCommands;

class BltCommands extends DrushCommands {

  /**
   * Check local settings and configuration to ensure that things are set up properly.
   *
   * @command blt:doctor
   * @aliases bdr,blt-doctor
   * @bootstrap full
   */
  public function doctor()
  {
    require_once __DIR__ . '/../../src/Drush/Command/BltDoctorCommand.php';
    $blt_doctor = new \Acquia\Blt\Drush\Command\BltDoctor();
    $blt_doctor->checkAll();
  }


}
