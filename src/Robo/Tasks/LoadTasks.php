<?php

namespace Acquia\Blt\Robo\Tasks;

/**
 * Load BLT's custom Robo tasks.
 */
trait LoadTasks {

  /**
   * @return DrushTask
   */
  protected function taskDrush() {
    return $this->task(DrushTask::class);
  }

}
