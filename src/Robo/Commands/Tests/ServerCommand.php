<?php

namespace Acquia\Blt\Robo\Commands\Tests;

use Acquia\Blt\Robo\Exceptions\BltException;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Defines commands in the "tests:server" namespace.
 */
class ServerCommand extends TestsCommandBase {

  protected $serverUrl;
  protected $serverPort;

  /**
   * Starts a temporary PHP web server.
   *
   * @command tests:server:start
   * @aliases tss
   */
  public function launchWebServer() {
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

  /**
   * Kills running PHP web server.
   *
   * @command tests:server:kill
   * @aliases tsk
   */
  public function killWebServer() {
    $this->getContainer()->get('executor')->killProcessByName('runserver');
    $this->getContainer()
      ->get('executor')
      ->killProcessByPort($this->getConfigValue('tests.server.port'));
  }

}
