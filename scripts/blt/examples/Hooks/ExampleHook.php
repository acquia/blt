<?php

namespace Acquia\Blt\Custom\Hooks;

use Acquia\Blt\Robo\BltTasks;
use Symfony\Component\Console\Event\ConsoleCommandEvent;

/**
 * This class defines example hooks.
 */
class ExampleHook extends BltTasks {

  /**
   * This will be called before the `custom:hello` command is executed.
   *
   * @hook command-event custom:hello
   */
  public function preExampleHello(ConsoleCommandEvent $event) {
    $command = $event->getCommand();
    $this->say("preCommandMessage hook: The {$command->getName()} command is about to run!");
  }

}
