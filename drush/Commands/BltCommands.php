<?php
namespace Drush\Commands\Blt;

use Drush\Commands\DrushCommands;

/**
 *
 * In addition to a commandfile like this one, you need a drush.services.yml
 * in root of your module.
 *
 * See these files for an example of injecting Drupal services:
 *   - http://cgit.drupalcode.org/devel/tree/src/Commands/DevelCommands.php
 *   - http://cgit.drupalcode.org/devel/tree/drush.services.yml
 */
class BltCommands extends DrushCommands {

  /**
   * Check local settings and configuration to ensure that things are set up properly.
   *
   * @command blt:doctor
   * @param array $options An associative array of options whose values come from cli, aliases, config, etc.
   * @aliases bdr,blt-doctor
   */
  public function doctor()
  {
    require_once __DIR__ . '/../src/Drush/Command/BltDoctorCommand.php';
    $blt_doctor = new \Acquia\Blt\Drush\Command\BltDoctor();
    $blt_doctor->checkAll();
  }


}
