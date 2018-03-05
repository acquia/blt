<?php

namespace Acquia\Blt\Robo\Hooks;

use Acquia\Blt\Robo\BltTasks;
use Symfony\Component\Console\Event\ConsoleCommandEvent;

/**
 * This class defines hooks that provide user interaction.
 *
 * These hooks typically use a Wizard to evaluate the validity of config or
 * state and guide the user toward resolving issues.
 */
class CommandEventHook extends BltTasks {

  /**
   * Disable any command listed in the `disable-target` config key.
   *
   * @hook command-event *
   */
  public function skipDisabledCommands(ConsoleCommandEvent $event) {
    $command = $event->getCommand();
    if ($this->isCommandDisabled($command->getName())) {
      $event->disableCommand();
    }

    // @todo Transmit analytics on command execution. Do the same in status hook.
  }

  /**
   * Issues warnings to user if their local environment is mis-configured.
   *
   * @hook command-event *
   */
  public function issueWarnings(ConsoleCommandEvent $event) {
    $command = $event->getCommand();
    $command_name = $command->getName();

    // The inspector tracks whether warnings have been issued because it is
    // shared in the container.
    $this->getInspector()->issueEnvironmentWarnings($command_name);
  }

}
