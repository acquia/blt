<?php

namespace Acquia\Blt\Robo\Commands\Setup;

use Acquia\Blt\Robo\BltTasks;

/**
 * Defines commands in the "recipes:cloud-hooks" namespace.
 */
class CloudHooksCommand extends BltTasks {

  /**
   * Installs Acquia cloud hooks.
   *
   * @command recipes:cloud-hooks:init
   * @aliases rchi setup:cloud-hooks
   */
  public function copy() {
    $destination = $this->getConfigValue('repo.root') . '/hooks';
    $this->say("Copying default Acquia cloud hooks into $destination...");
    // This WILL overwrite files is source files are newer.
    $result = $this->taskCopyDir([
      $this->getConfigValue('blt.root') . '/scripts/cloud-hooks/hooks' => $destination,
    ])
      ->run();
    $this->taskFilesystemStack()
      ->chmod($destination, 0755, 0000, TRUE)
      ->run();

    return $result;
  }

}
