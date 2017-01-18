<?php

namespace Acquia\Blt\Robo\LocalEnvironment;

/**
 *
 */
trait LocalEnvironmentAwareTrait {
  /**
   * @var \Acquia\Blt\Robo\LocalEnvironment\LocalEnvironment
   */
  private $localEnvironment;

  /**
   *
   */
  public function setLocalEnvironment(LocalEnvironment $local_environment) {
    $this->localEnvironment = $local_environment;
  }

  /**
   * @return \Acquia\Blt\Robo\LocalEnvironment\LocalEnvironment
   */
  public function getLocalEnvironment() {
    return $this->localEnvironment;
  }

}
