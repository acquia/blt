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
    $this->say("Setting up local environment for site <comment>{$this->getConfigValue('site')}</comment>.");
    if ($this->getConfigValue('drush.alias')) {
      $this->say("Using drush alias <comment>@{$this->getConfigValue('drush.alias')}</comment>");
    }

    $commands = [
      'setup:build',
      'drupal:init:hash-salt',
    ];

    switch ($this->getConfigValue('setup.strategy')) {
      case 'install':
        $commands[] = 'drupal:install';
        $commands[] = 'drupal:toggle:modules';
        break;

      case 'sync':
        $commands[] = 'drupal:sync';
        break;

      case 'import':
        $commands[] = 'drupal:sql:import';
        $commands[] = 'drupal:update';
        break;
    }

    $commands[] = 'blt:init:shell-alias';

    $this->invokeCommands($commands);
  }

}
