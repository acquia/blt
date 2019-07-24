<?php

namespace Acquia\Blt\Robo\Hooks;

use Acquia\Blt\Robo\BltTasks;
use Consolidation\AnnotatedCommand\CommandData;

/**
 * Starts and kills Drush webserver for @launchWebServer annotation.
 */
class WebServerHook extends BltTasks {

  /**
   * Launch web server.
   *
   * @hook pre-command @launchWebServer
   */
  public function launchWebServer(CommandData $commandData) {
    if ($this->getConfigValue('tests.run-server')) {
      $this->invokeCommand('tests:server:start');
    }
  }

  /**
   * Kill web server.
   *
   * @hook post-command @launchWebServer
   */
  public function killWebServer($result, CommandData $commandData) {
    if ($this->getConfigValue('tests.run-server')) {
      $this->invokeCommand('tests:server:kill');
    }
  }

}
