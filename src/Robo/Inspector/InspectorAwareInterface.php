<?php

namespace Acquia\Blt\Robo\Inspector;

/**
 *
 */
interface InspectorAwareInterface {

  /**
   * @param \Acquia\Blt\Robo\Inspector\Inspector $local_environment
   *
   * @return mixed
   */
  public function setInspector(Inspector $local_environment);

}
