<?php

namespace Acquia\Blt\Robo\Commands\Drupal;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Exceptions\BltException;

/**
 * Defines commands in the "drupal:sql:import" namespace.
 */
class SqlCommand extends BltTasks {

  /**
   * Imports a .sql file into the Drupal database.
   *
   * @command drupal:sql:import
   *
   * @aliases dsi
   *
   * @validateDrushConfig
   */
  public function import() {
    $task = $this->taskDrush()
      ->drush('sql-drop')
      ->drush('sql-cli < ' . $this->getConfigValue('setup.dump-file'));
    $result = $task->run();
    $exit_code = $result->getExitCode();

    if ($exit_code) {
      throw new BltException("Unable to import setup.dump-file.");
    }
  }

}
