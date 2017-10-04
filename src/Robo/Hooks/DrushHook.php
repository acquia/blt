<?php

namespace Acquia\Blt\Robo\Hooks;

use Acquia\Blt\Robo\BltTasks;
use Symfony\Component\Console\Event\ConsoleCommandEvent;

/**
 * Validate Drush configuration for failed commands.
 */
class DrushHook extends BltTasks {

  /**
   * Corrects drush aliases when inside of the VM.
   *
   * The VM alias is not available inside the VM.
   *
   * @hook command-event *
   */
  public function drushVmAlias(ConsoleCommandEvent $commandData) {
    if ($this->getInspector()->isVmCli()) {
      $this->getConfig()->set('drush.alias', '');
      $this->getConfig()->set('drush.aliases.local', 'self');
    }
  }

}
