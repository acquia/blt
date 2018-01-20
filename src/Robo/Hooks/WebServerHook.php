<?php

namespace Acquia\Blt\Robo\Hooks;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Common\IO;
use Acquia\Blt\Robo\Config\ConfigAwareTrait;
use Acquia\Blt\Robo\Inspector\InspectorAwareTrait;
use Consolidation\AnnotatedCommand\CommandData;
use League\Container\ContainerAwareTrait;
use Psr\Log\LoggerAwareTrait;

/**
 * Starts and kills Drush webserver for @launchWebServer annotation.
 */
class WebServerHook extends BltTasks {

  use ConfigAwareTrait;
  use ContainerAwareTrait;
  use LoggerAwareTrait;
  use InspectorAwareTrait;
  use IO;

  protected $serverUrl;
  protected $serverPort;

  /**
   * @hook pre-command @launchWebServer
   */
  public function launchWebServer(CommandData $commandData) {
    if ($this->getConfigValue('tests.run-server')) {
      $this->invokeCommand('tests:server:start');
    }
  }

  /**
   * @hook post-command @launchWebServer
   */
  public function killWebServer($result, CommandData $commandData) {
    if ($this->getConfigValue('tests.run-server')) {
      $this->invokeCommand('tests:server:kill');
    }
  }

}
