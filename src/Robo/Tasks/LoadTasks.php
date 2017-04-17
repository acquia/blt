<?php

namespace Acquia\Blt\Robo\Tasks;

trait LoadTasks
{
  /**
   * @return Drush
   */
  protected function taskDrush()
  {
    return $this->task(Drush::class);
  }
}
