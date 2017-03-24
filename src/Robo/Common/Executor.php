<?php

namespace Acquia\Blt\Robo\Common;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Config\ConfigAwareTrait;
use GuzzleHttp\Client;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LogLevel;
use Robo\Collection\CollectionBuilder;
use Robo\Common\ProcessExecutor;
use Robo\Robo;
use Symfony\Component\Console\Helper\ProcessHelper;
use Symfony\Component\Console\Output\OutputInterface;
use Robo\Contract\ConfigAwareInterface;
use Robo\Contract\IOAwareInterface;
use Symfony\Component\Process\Process;

/**
 *
 */
class Executor implements ConfigAwareInterface, IOAwareInterface, LoggerAwareInterface {

  use ConfigAwareTrait;
  use IO;
  use LoggerAwareTrait;

  /** @var BltTasks */
  protected $builder;

  /**
   * Executor constructor.
   *
   * @param \Robo\Collection\CollectionBuilder $builder
   */
  public function __construct(CollectionBuilder $builder) {
    $this->builder = $builder;
  }

  /**
   * @return \Acquia\Blt\Robo\BltTasks
   */
  public function getBuilder() {
    return $this->builder;
  }

  /**
   * @param $command
   *
   * @return \Robo\Task\Base\Exec
   */
  public function taskExec($command) {
    return $this->builder->taskExec($command);
  }

  /**
   * @param $command
   *
   * @return ProcessExecutor
   */
  public function drush($command) {
    // @todo Set to silent if verbosity is less than very verbose.
    $bin = $this->getConfigValue('composer.bin');
    /** @var ProcessExecutor $process_executor */
    $process_executor = Robo::process(new Process("$bin/drush $command"));
    return $process_executor->dir($this->getConfigValue('docroot'))
      ->interactive(false)
      ->printOutput(false)
      ->printMetadata(false);
  }

  /**
   * @param $command
   *
   * @return ProcessExecutor
   */
  public function execute($command) {
    $process_executor = Robo::process(new Process($command));
    return $process_executor->dir($this->getConfigValue('repo.root'))
      ->interactive(false)
      ->printOutput(false)
      ->printMetadata(false);
  }

  /**
   * @param $port
   */
  public function killProcessByPort($port) {
    $this->logger->info("Killing all processes on port $port");
    // This is allowed to fail.
    // @todo Replace with standardized call to Symfony Process.
    exec("lsof -ti tcp:$port | xargs kill l 2>&1");
  }

  /**
   * @param $name
   */
  public function killProcessByName($name) {
    $this->logger->info("Killing all processing containing string '$name'");
    // This is allowed to fail.
    // @todo Replace with standardized call to Symfony Process.
    exec("ps aux | grep -i $name | grep -v grep | awk '{print $2}' | xargs kill -9 2>&1");
    //exec("ps aux | awk '/$name/ {print $2}' 2>&1 | xargs kill -9");
  }

  /**
   * @param $url
   *
   * @return bool
   */
  public function waitForUrlAvailable($url) {
    $this->wait([$this, 'checkUrl'], [$url], "Waiting for response from $url...");
  }

  /**
   * @param callable $callable
   * @param $args
   *
   * @return bool
   * @throws \Exception
   */
  public function wait($callable, $args, $message = '') {
    $maxWait = 15 * 1000;
    $checkEvery = 1 * 1000;
    $start = microtime(true) * 1000;
    $end = $start + $maxWait;

    if (!$message) {
      $method_name = is_array($callable) ? $callable[1] : $callable;
      $message = "Waiting for $method_name() to return true.";
    }

    // For some reason we can't reuse $start here.
    while (microtime(true) * 1000 < $end) {
      $this->logger->info($message);
      try {
        if (call_user_func_array($callable, $args)) {
          return TRUE;
        }
      }
      catch (\Exception $e) {
        $this->logger->debug($e->getMessage());
      }
      usleep($checkEvery * 1000);
    }

    throw new \Exception("Timed out");
  }

  /**
   * @param $url
   *
   * @return int
   */
  public function checkUrl($url) {
    try {
      $client = new Client();
      $res = $client->request('GET', $url, [
        'connection_timeout' => 2,
        'timeout' => 2,
      ]);
      return $res->getStatusCode() == 200;
    } catch (\Exception $e) {

    }
    return FALSE;
  }
}
