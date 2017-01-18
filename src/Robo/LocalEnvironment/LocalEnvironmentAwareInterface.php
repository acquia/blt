<?php

namespace Acquia\Blt\Robo\LocalEnvironment;

/**
 *
 */
interface LocalEnvironmentAwareInterface {

  /**
   * @param \Acquia\Blt\Robo\LocalEnvironment\LocalEnvironment $local_environment
   *
   * @return mixed
   */
  public function setLocalEnvironment(LocalEnvironment $local_environment);

}
