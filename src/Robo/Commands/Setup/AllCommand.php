<?php

namespace Acquia\Blt\Robo\Commands\Setup;

use Acquia\Blt\Robo\BltTasks;

/**
 * Defines commands in the "setup:all" namespace.
 */
class AllCommand extends BltTasks {

  /**
   * Executes source:build:* and installs Drupal via setup.strategy.
   *
   * @command setup
   * @executeInVm
   */
  public function setup() {
    $this->say("Setting up local environment for site <comment>{$this->getConfigValue('site')}</comment>.");
    if ($this->getConfigValue('drush.alias')) {
      $this->say("Using drush alias <comment>@{$this->getConfigValue('drush.alias')}</comment>");
    }

    $commands = [
      'source:build',
      'drupal:deployment-identifier:init',
    ];

    switch ($this->getConfigValue('setup.strategy')) {
      case 'install':
        $commands[] = 'drupal:install';
        $commands[] = 'drupal:toggle:modules';
        break;

      case 'sync':
        $commands[] = 'drupal:sync:default:site';
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
