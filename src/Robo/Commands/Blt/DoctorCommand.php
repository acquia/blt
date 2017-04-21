<?php

namespace Acquia\Blt\Robo\Commands\Blt;

use Acquia\Blt\Robo\BltTasks;
use Robo\Contract\VerbosityThresholdInterface;

/**
 * Defines commands in the "blt:doctor" namespace.
 */
class DoctorCommand extends BltTasks {

  /**
   * @command doctor
   */
  public function doctor() {
    $drush_bin = $this->getConfigValue('composer.bin') . '/drush';
    $include_dir = $this->getConfigValue('blt.root') . '/drush';
    $alias = $this->getConfigValue('drush.alias');
    $this->taskExec("$drush_bin @$alias --include=$include_dir blt-doctor")
      ->dir($this->getConfigValue('docroot'))
      ->detectInteractive()
      ->run();
  }

}
