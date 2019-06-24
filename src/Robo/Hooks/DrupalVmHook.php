<?php

namespace Acquia\Blt\Robo\Hooks;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Exceptions\BltException;

/**
 * This class defines hooks that provide user interaction.
 */
class DrupalVmHook extends BltTasks {

  /**
   * Ask whether user would like to execute on host machine.
   *
   * @hook interact @executeInVm
   *
   * @throws \Acquia\Blt\Robo\Exceptions\BltException
   */
  public function interactExecuteOnHost() {
    if (!$this->getInspector()->isVmCli() && $this->getInspector()->isDrupalVmLocallyInitialized() && $this->getConfigValue('vm.blt-in-vm')) {
      $this->logger->warning("Drupal VM is locally initialized, but you are not inside the VM.");
      $this->logger->warning("You should execute all BLT commands from within Drupal VM.");
      $this->logger->warning("Use <comment>vagrant ssh</comment> to enter the VM.");
      $continue = $this->confirm("Do you want to continue and execute this command on the host machine?");
      if (!$continue) {
        throw new BltException("Command terminated by user.");
      }
    }
  }

}
