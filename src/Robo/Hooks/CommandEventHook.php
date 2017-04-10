<?php

namespace Acquia\Blt\Robo\Hooks;

use Acquia\Blt\Robo\Common\ArrayManipulator;
use Acquia\Blt\Robo\Common\IO;
use Acquia\Blt\Robo\Config\ConfigAwareTrait;
use Acquia\Blt\Robo\Console\ConfigInput;
use Consolidation\AnnotatedCommand\AnnotationData;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Robo\Contract\ConfigAwareInterface;
use Robo\Contract\IOAwareInterface;
use Robo\Tasks;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This class defines hooks that provide user interaction.
 *
 * These hooks typically use a Wizard to evaluate the validity of config or
 * state and guide the user toward resolving issues.
 */
class CommandEventHook extends Tasks implements IOAwareInterface, ConfigAwareInterface, LoggerAwareInterface {

  use ConfigAwareTrait;
  use LoggerAwareTrait;

  /**
   * Disable any command listed in the `disable-target` config key.
   *
   * @hook command-event *
   */
  public function skipDisabledCommands(ConsoleCommandEvent $event) {
    $command = $event->getCommand();
    $disabled_commands_config = $this->getConfigValue('disable-targets');
    if ($disabled_commands_config) {
      $disabled_commands = ArrayManipulator::flattenMultidimensionalArray($disabled_commands_config, ':');
      if (in_array($command->getName(), $disabled_commands) && $disabled_commands[$command->getName()]) {
        $event->disableCommand();
        $this->output()->writeln("The {$command->getName()} command has been disabled. Skipping execution.");
      }
    }
  }

}
