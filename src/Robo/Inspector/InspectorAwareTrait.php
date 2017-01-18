<?php

namespace Acquia\Blt\Robo\Inspector;

/**
 *
 */
trait InspectorAwareTrait {
  /**
   * @var \Acquia\Blt\Robo\Inspector\Inspector
   */
  private $Inspector;

  /**
   *
   */
  public function setInspector(Inspector $local_environment) {
    $this->Inspector = $local_environment;
  }

  /**
   * @return \Acquia\Blt\Robo\Inspector\Inspector
   */
  public function getInspector() {
    return $this->Inspector;
  }

}
