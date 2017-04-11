<?php

namespace Acquia\Blt\Custom\Hooks;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Common\IO;
use Acquia\Blt\Robo\Config\ConfigAwareTrait;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Robo\Contract\ConfigAwareInterface;
use Robo\Contract\IOAwareInterface;
use Symfony\Component\Console\Event\ConsoleCommandEvent;

/**
 * This class defines example hooks.
 */
class ExampleHook extends BltTasks implements IOAwareInterface, ConfigAwareInterface, LoggerAwareInterface {

  use ConfigAwareTrait;
  use LoggerAwareTrait;
  use IO;

  /**
   * This will be called before the `example:hello` command is executed.
   *
   * @hook command-event example:hello
   */
  public function preExampleHello(ConsoleCommandEvent $event) {
    $command = $event->getCommand();
    $this->say("preCommandMessage hook: The {$command->getName()} command is about to run!");
  }

}
