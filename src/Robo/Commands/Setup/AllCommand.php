<?php

namespace Acquia\Blt\Robo\Commands\Setup;

use Acquia\Blt\Robo\BltTasks;

/**
 * Defines commands in the "setup:all" namespace.
 */
class AllCommand extends BltTasks {

  /**
   * Install dependencies, builds docroot, installs Drupal.
   *
   * @command setup
   *
   * @aliases setup:all
   */
  public function setup() {
    $this->say("Setting up local environment for site '{$this->getConfigValue('site')}' using drush alias @{$this->getConfigValue('drush.alias')}");

    $commands = [
      'setup:build',
      'setup:hash-salt',
    ];

    switch ($this->getConfigValue('setup.strategy')) {
      case 'install':
        $commands[] = 'setup:drupal:install';
        $commands[] = 'setup:toggle-modules';
        break;

      case 'sync':
        $commands[] = 'sync:refresh';
        break;

      case 'import':
        $commands[] = 'setup:import';
        $commands[] = 'setup:update';
        break;
    }

    $commands[] = 'install-alias';

    $this->invokeCommands($commands);
  }

}
