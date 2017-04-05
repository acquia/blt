<?php

namespace Acquia\Blt\Robo\Inspector;

/**
 *
 */
trait InspectorAwareTrait {
  /**
   * @var \Acquia\Blt\Robo\Inspector\Inspector
   */
  private $inspector;

  /**
   *
   */
  public function setInspector(Inspector $inspector) {
    $this->inspector = $inspector;
  }

  /**
   * @return \Acquia\Blt\Robo\Inspector\Inspector
   */
  public function getInspector() {
    return $this->inspector;
  }

}
