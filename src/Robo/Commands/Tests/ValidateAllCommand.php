<?php

namespace Acquia\Blt\Robo\Commands\Tests;

use Acquia\Blt\Robo\BltTasks;

/**
 * Defines commands in the "validate:all*" namespace.
 */
class ValidateAllCommand extends BltTasks {

  /**
   * Runs all code validation commands.
   *
   * @command validate
   * @aliases validate:all
   * @hidden
   */
  public function all() {
    $commands = [
      'tests:composer:validate',
      'tests:php:lint',
      'tests:phpcs:sniff:all',
      'tests:yaml:lint:all',
      'tests:twig:lint:all',
    ];

    // To enable this command, set validate.acsf to TRUE in blt.yml.
    if ($this->getConfigValue('validate.acsf') == TRUE) {
      $commands[] = 'tests:acsf:validate';
    }

    // To enable this command, set to TRUE in blt.yml.
    if ($this->getConfigValue('validate.deprecation.modules') == TRUE) {
      $commands[] = 'tests:deprecated:modules';
    }

    // To enable this command, set to TRUE in blt.yml.
    if ($this->getConfigValue('validate.deprecation.themes') == TRUE) {
      $commands[] = 'tests:deprecated:themes';
    }

    $status_code = $this->invokeCommands($commands);
    return $status_code;
  }

}
