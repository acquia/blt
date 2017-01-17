<?php

namespace Acquia\Blt\Robo;

use Acquia\Blt\Robo\Common\IO;
use Acquia\Blt\Robo\LocalEnvironment\LocalEnvironment;
use Acquia\Blt\Robo\LocalEnvironment\LocalEnvironmentInterface;
use Robo\Tasks;

class BltTasks extends Tasks implements LocalEnvironmentInterface {

  use IO;

  /** @var \Acquia\Blt\Robo\LocalEnvironment\LocalEnvironment */
  protected $localEnvironment;


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
