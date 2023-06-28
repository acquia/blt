<?php

namespace Acquia\Blt\Robo\Commands\Validate;

use Acquia\Blt\Robo\BltTasks;

/**
 * Defines commands in the "validate*" namespace.
 */
class ValidateAllCommand extends BltTasks {

  /**
   * Runs all code validation commands.
   *
   * @command validate
   * @hidden
   *
   * @throws \Acquia\Drupal\RecommendedSettings\Exceptions\SettingsException
   */
  public function all() {
    return $this->invokeNamespace('validate');
  }

}
