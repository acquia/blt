<?php

namespace Acquia\Blt\Robo\Hooks;

use Acquia\Blt\Robo\BltTasks;
use Consolidation\AnnotatedCommand\CommandData;

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
  public function drushVmAlias(CommandData $commandData) {
    if ($this->getInspector()->isVmCli()) {
      $this->getConfig()->set('drush.alias', '');
      $this->getConfig()->set('drush.alias.local', 'self');
    }
  }

}
