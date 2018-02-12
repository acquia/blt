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
   * @aliases validate:all
   * @hidden
   */
  public function all() {
    $status_code = $this->invokeCommands([
      'tests:composer:validate',
      'tests:php:lint',
      'tests:phpcs:sniff:all',
      'tests:yaml:lint:all',
      'tests:twig:lint:all',
    ]);

    return $status_code;
  }

}
