<?php

namespace Example\Blt\Custom\Commands;

use Acquia\Blt\Robo\BltTasks;

/**
 * Defines commands in the "custom" namespace.
 */
class ExampleCommands extends BltTasks {

  /**
   * Print "Hello world!" to the console.
   *
   * @command custom:hello
   * @description This is an example command.
   */
  public function hello() {
    $this->say("Hello world!");
  }

}
