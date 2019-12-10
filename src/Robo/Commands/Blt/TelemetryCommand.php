<?php

namespace Acquia\Blt\Robo\Commands\Blt;

use Acquia\Blt\Robo\Blt;
use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Common\UserConfig;

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
    $userConfig = new UserConfig(Blt::configDir());
    $userConfig->setTelemetryEnabled(TRUE);
    $this->say($userConfig::OPT_IN_MESSAGE);
  }

  /**
   * Disables telemetry.
   *
   * @command blt:telemetry:disable
   */
  public function telemetryDisable() {
    $userConfig = new UserConfig(Blt::configDir());
    $userConfig->setTelemetryEnabled(FALSE);
    $this->say($userConfig::OPT_OUT_MESSAGE);
  }

}
