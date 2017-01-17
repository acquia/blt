<?php

namespace Acquia\Blt\Robo\LocalEnvironment;

use Acquia\Blt\Robo\LocalEnvironment\LocalEnvironment;

interface LocalEnvironmentInterface {

  /**
   * @param \Acquia\Blt\Robo\LocalEnvironment\LocalEnvironment $local_environment
   *
   * @return mixed
   */
  public function setLocalEnvironment(LocalEnvironment $local_environment);
}
