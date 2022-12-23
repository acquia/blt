<?php

namespace Acquia\Blt\Robo\Commands\Blt;

use Acquia\Blt\Robo\BltTasks;

/**
 * Defines commands in the 'blt:telemetry' namespace.
 */
class TelemetryCommand extends BltTasks {

  /**
   * Enables telemetry.
   *
   * @command blt:telemetry:enable
   */
  public function telemetryEnable() {
    $this->say('Telemetry has been removed from BLT, this command does nothing.');
  }

  /**
   * Disables telemetry.
   *
   * @command blt:telemetry:disable
   */
  public function telemetryDisable() {
    $this->say('Telemetry has been removed from BLT, this command does nothing.');
  }

}
