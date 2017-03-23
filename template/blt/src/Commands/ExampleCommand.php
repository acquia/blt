<?php

namespace Acquia\Blt\Custom\Commands;

use Acquia\Blt\Robo\BltTasks;

/**
 * Defines commands in the "example" namespace.
 */
class ExampleCommand extends BltTasks {

  /**
   * Check local Drupal installation for security updates.
   *
   * @command example:hello
   * @description This is an example command.
   */
  public function hello() {
    $this->say("Hello world!");
  }

}
