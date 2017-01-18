<?php

namespace Acquia\Blt\Robo\Common;

/**
 *
 */
interface ExecutorAwareInterface {

  /**
   * @param \Acquia\Blt\Robo\Common\Executor $executor
   *
   * @return mixed
   */
  public function setExecutor(Executor $executor);

  /**
   * @return \Acquia\Blt\Robo\Common\Executor
   */
  public function getExecutor();

}
