<?php

namespace Acquia\Blt\Robo\Commands\Vm;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Exceptions\BltException;

/**
 * Defines commands in the "vm:lando" namespace.
 */
class LandoCommand extends BltTasks {

  /**
   * Configures and boots a Lando VM.
   *
   * @command vm:lando:init
   *
   * @throws \BltException
   */
  public function vm() {
    if (!$this->getInspector()->commandExists('lando')) {
      throw new BltException("lando must be installed on this machine.");
    }

    $this->taskFilesystemStack()
      ->copy($this->getConfigValue('blt.root') . '/scripts/lando/.lando.yml',
        $this->getConfigValue('repo.root') . '/.lando.yml'
        )
      ->run();
    $this->getConfig()->expandFileProperties($this->getConfigValue('repo.root') . '/.lando.yml');
    $this->taskExecStack()
      ->dir($this->getConfigValue('repo.root'))
      ->exec('lando ssh -u root -c "ln -s /app/vendor/bin/blt /usr/bin/blt"')
      ->run();
  }

}
