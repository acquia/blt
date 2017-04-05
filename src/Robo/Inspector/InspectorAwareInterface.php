<?php

namespace Acquia\Blt\Robo\Inspector;

/**
 *
 */
interface InspectorAwareInterface {

  /**
   * @param \Acquia\Blt\Robo\Inspector\Inspector $inspector
   *
   * @return mixed
   */
  public function setInspector(Inspector $inspector);

}
