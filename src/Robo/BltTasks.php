<?php

namespace Acquia\Blt\Robo;

use Acquia\Blt\Robo\Common\IO;
use Acquia\Blt\Robo\Common\LocalEnvironment;
use Acquia\Blt\Robo\Common\LocalEnvironmentValidator;
use Robo\Tasks;

class BltTasks extends Tasks {

  use IO;

  /** @var \Acquia\Blt\Robo\Common\LocalEnvironment */
  protected $localEnvironment;
  protected $localEnvironmentValidator;

  /**
   * RoboFile constructor.
   */
  public function __construct() {
    $this->localEnvironment = new LocalEnvironment();
    $this->localEnvironmentValidator = new LocalEnvironmentValidator($this->localEnvironment);
  }

  /**
   * @return \Acquia\Blt\Robo\Common\LocalEnvironment
   */
  public function getLocalEnvironment() {
    return $this->localEnvironment;
  }

  /**
   * @return \Acquia\Blt\Robo\Common\LocalEnvironmentValidator
   */
  public function getLocalEnvironmentValidator() {
    return $this->localEnvironmentValidator;
  }

}
