<?php

namespace Acquia\Blt\Robo\Tasks;

/**
 * Load BLT's custom Robo tasks.
 */
trait LoadTasks {

  /**
   * @return \Acquia\Blt\Robo\Tasks\DrushTask
   */
  protected function taskDrush() {
    $task = $this->task(DrushTask::class);
    $task->setInput($this->input());

    return $task;
  }

}
