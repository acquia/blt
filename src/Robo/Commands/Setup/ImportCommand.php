<?php

namespace Acquia\Blt\Robo\Commands\Setup;

use Acquia\Blt\Robo\BltTasks;

/**
 * Defines commands in the "drupal:sql:import" namespace.
 */
class ImportCommand extends BltTasks {

  /**
   * Imports a .sql file into the Drupal database.
   *
   * @command drupal:sql:import
   *
   * @aliases dsi
   *
   * @validateDrushConfig
   * @executeInVm
   *
   * @throws \Robo\Exception\TaskException
   */
  public function import() {
    $task = $this->taskDrush()
      ->drush('sql-drop')
      ->drush('sql-cli < ' . $this->getConfigValue('setup.dump-file'));
    $task->run();
  }

}
