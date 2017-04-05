<?php

namespace Acquia\Blt\Robo\Common;

/**
 *
 */
trait ExecutorAwareTrait {

  /**
   * @var Executor
   */
  private $executor;

  /**
   * @param \Acquia\Blt\Robo\Common\Executor $executor
   */
  public function setExecutor(Executor $executor) {
    $this->executor = $executor;
  }

  /**
   * @return \Acquia\Blt\Robo\Common\Executor
   */
  public function getExecutor() {
    return $this->executor;
  }

}
