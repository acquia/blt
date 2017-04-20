<?php

namespace Acquia\Blt\Robo\Common;

/**
 * Requires getters and setters for $this->executor.
 */
interface ExecutorAwareInterface {

  /**
   * Sets $this->executor.
   *
   * @param \Acquia\Blt\Robo\Common\Executor $executor
   *   Process executor.
   */
  public function setExecutor(Executor $executor);

  /**
   * Gets $this->executor.
   *
   * @return \Acquia\Blt\Robo\Common\Executor
   *   Process executor.
   */
  public function getExecutor();

}
