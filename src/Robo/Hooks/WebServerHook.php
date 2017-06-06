<?php

namespace Acquia\Blt\Robo\Hooks;

use Acquia\Blt\Robo\Common\IO;
use Acquia\Blt\Robo\Config\ConfigAwareTrait;
use Acquia\Blt\Robo\Inspector\InspectorAwareInterface;
use Acquia\Blt\Robo\Inspector\InspectorAwareTrait;
use League\Container\ContainerAwareInterface;
use League\Container\ContainerAwareTrait;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Robo\Contract\ConfigAwareInterface;

/**
 *
 */
class WebServerHook implements ConfigAwareInterface, ContainerAwareInterface, LoggerAwareInterface, InspectorAwareInterface {

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
  public function launchWebServer() {
    if ($this->getConfigValue('behat.run-server')) {
      $this->serverUrl = $this->getConfigValue('behat.server.url');
      $this->killWebServer();
      $this->say("Launching PHP's internal web server via drush.");
      $this->logger->info("Running server at $this->serverUrl...");
      $this->getContainer()->get('executor')->drush("runserver $this->serverUrl > /dev/null")->background(TRUE)->run();
      $this->getContainer()->get('executor')->waitForUrlAvailable($this->serverUrl);
    }
  }

  /**
   * @hook post-command @launchWebServer
   */
  public function killWebServer() {
    $this->getContainer()->get('executor')->killProcessByName('runserver');
    $this->getContainer()->get('executor')->killProcessByPort($this->getConfigValue('behat.server.port'));
  }

}
