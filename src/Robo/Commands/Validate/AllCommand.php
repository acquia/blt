<?php

namespace Acquia\Blt\Robo\Commands\Validate;

use Acquia\Blt\Robo\BltTasks;

/**
 * Defines commands in the "validate:all*" namespace.
 */
class AllCommand extends BltTasks {

  /**
   * Runs all code validation commands.
   *
   * @command validate
   *
   * @aliases validate:all
   */
  public function all() {
    $status_code = $this->invokeCommands([
      'validate:composer',
      'validate:lint',
      'validate:phpcs',
      'validate:yaml',
      'validate:twig',
    ]);

    return $status_code;
  }

}
