<?php

namespace Acquia\Blt\Robo\Common;

/**
 * Provides getters and setters for $this->executor.
 */
trait ExecutorAwareTrait {

  /**
   * Process executor.
   *
   * @var Executor
   */
  private $executor;

  /**
   * Sets $this->executor.
   *
   * @param \Acquia\Blt\Robo\Common\Executor $executor
   *   Process executor.
   */
  public function setExecutor(Executor $executor) {
    $this->executor = $executor;
  }

  /**
   * Gets $this->executor.
   *
   * @return \Acquia\Blt\Robo\Common\Executor
   *   Process executor.
   */
  public function getExecutor() {
    return $this->executor;
  }

}
