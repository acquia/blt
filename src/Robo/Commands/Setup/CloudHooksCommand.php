<?php

namespace Acquia\Blt\Robo\Commands\Setup;

use Acquia\Blt\Robo\BltTasks;

/**
 * Defines commands in the "setup:build" namespace.
 */
class CloudHooksCommand extends BltTasks {

  /**
   * Installs Acquia cloud hooks.
   *
   * @command setup:cloud-hooks
   */
  public function copy() {
    $this->taskCopyDir([
      $this->getConfigValue('blt.root') . '/scripts/cloud-hooks/hooks' => $this->getConfigValue('repo.root') . '/hooks',
    ])
      ->run();
  }

}
