<?php

namespace Acquia\Blt\Robo\Hooks;

use Acquia\Blt\Robo\Common\IO;
use Acquia\Blt\Robo\Config\ConfigAwareTrait;
use Acquia\Blt\Robo\Exceptions\BltException;
use Acquia\Blt\Robo\Inspector\InspectorAwareInterface;
use Acquia\Blt\Robo\Inspector\InspectorAwareTrait;
use League\Container\ContainerAwareInterface;
use League\Container\ContainerAwareTrait;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Robo\Contract\ConfigAwareInterface;
use Symfony\Component\Filesystem\Filesystem;

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
    if ($this->getConfigValue('tests.run-server')) {
      $this->serverUrl = $this->getConfigValue('tests.server.url');
      $this->killWebServer();
      $this->say("Launching PHP's internal web server via drush.");
      $this->logger->info("Running server at $this->serverUrl...");

      $fs = new Filesystem();
      $fs->mkdir([$this->getConfigValue('repo.root') . '/tmp']);
      $log_file = $this->getConfigValue('repo.root') . '/tmp/runserver.log';
      if (file_exists($log_file)) {
        unlink($log_file);
      }

      /** @var \Acquia\Blt\Robo\Common\Executor $executor */
      $executor = $this->getContainer()->get('executor');
      $result = $executor
        ->drush("runserver $this->serverUrl > $log_file 2>&1")
        ->background(TRUE)
        ->run();

      try {
        $executor->waitForUrlAvailable($this->serverUrl);
      }
      catch (\Exception $e) {
        if (!$result->wasSuccessful() && file_exists($log_file)) {
          $output = file_get_contents($log_file);
          throw new BltException($e->getMessage() . "\n" . $output);
        }
      }
    }
  }

  /**
   * @hook post-command @launchWebServer
   */
  public function killWebServer() {
    $this->getContainer()->get('executor')->killProcessByName('runserver');
    $this->getContainer()->get('executor')->killProcessByPort($this->getConfigValue('tests.server.port'));
  }

}
