<?php

namespace Acquia\Blt\Robo;

use Robo\Robo;

class BltRobo extends Robo {

  /**
   * Override parent createDefaultApplication() to use custom application.
   */
  public static function createDefaultApplication($appName = null, $appVersion = null)
  {
    $appName = $appName ?: self::APPLICATION_NAME;
    $appVersion = $appVersion ?: self::VERSION;

    $app = new BltApplication($appName, $appVersion);
    $app->setAutoExit(false);
    return $app;
  }

}
