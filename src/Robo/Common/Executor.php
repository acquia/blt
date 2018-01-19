<?php

namespace Acquia\Blt\Robo\Common;

use Acquia\Blt\Robo\Config\ConfigAwareTrait;
use Acquia\Blt\Robo\Exceptions\BltException;
use GuzzleHttp\Client;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Robo\Collection\CollectionBuilder;
use Robo\Contract\VerbosityThresholdInterface;
use Robo\Robo;
use Robo\Contract\ConfigAwareInterface;
use Robo\Contract\IOAwareInterface;
use Symfony\Component\Process\Process;

/**
 * A class for executing commands.
 *
 * This allows non-Robo-command classes to execute commands easily.
 */
class Executor implements ConfigAwareInterface, IOAwareInterface, LoggerAwareInterface {

  use ConfigAwareTrait;
  use IO;
  use LoggerAwareTrait;

  /**
   * A copy of the Robo builder.
   *
   * @var \Acquia\Blt\Robo\BltTasks*/
  protected $builder;

  /**
   * Executor constructor.
   *
   * @param \Robo\Collection\CollectionBuilder $builder
   *   This is a copy of the collection builder, required for calling various
   *   Robo tasks from non-command files.
   */
  public function __construct(CollectionBuilder $builder) {
    $this->builder = $builder;
  }

  /**
   * Returns $this->builder.
   *
   * @return \Acquia\Blt\Robo\BltTasks
   *   The builder.
   */
  public function getBuilder() {
    return $this->builder;
  }

  /**
   * Wrapper for taskExec().
   *
   * @param string $command
   *   The command to execute.
   *
   * @return \Robo\Task\Base\Exec
   *   The task. You must call run() on this to execute it!
   */
  public function taskExec($command) {
    return $this->builder->taskExec($command);
  }

  /**
   * Executes a drush command.
   *
   * @param string $command
   *   The command to execute, without "drush" prefix.
   *
   * @return \Robo\Common\ProcessExecutor
   *   The unexecuted process.
   */
  public function drush($command) {
    // @todo Set to silent if verbosity is less than very verbose.
    $bin = $this->getConfigValue('composer.bin');
    /** @var \Robo\Common\ProcessExecutor $process_executor */
    $drush_alias = $this->getConfigValue('drush.alias');
    $command_string = "'$bin/drush' @$drush_alias $command";

    if ($this->input()->hasOption('yes') && $this->input()->getOption('yes')) {
      $command_string .= ' -y';
    }

    // URIs do not work on remote drush aliases in Drush 9. Instead, it is
    // expected that the alias define the uri in its configuration.
    if ($drush_alias != 'self') {
      $command_string .= ' --uri=' . $this->getConfigValue('site');
    }

    $process_executor = Robo::process(new Process($command_string));

    return $process_executor->dir($this->getConfigValue('docroot'))
      ->interactive(FALSE)
      ->printOutput(TRUE)
      ->printMetadata(TRUE)
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERY_VERBOSE);
  }

  /**
   * Executes a command.
   *
   * @param string $command
   *   The command.
   *
   * @return \Robo\Common\ProcessExecutor
   *   The unexecuted command.
   */
  public function execute($command) {
    /** @var \Robo\Common\ProcessExecutor $process_executor */
    $process_executor = Robo::process(new Process($command));
    return $process_executor->dir($this->getConfigValue('repo.root'))
      ->printOutput(FALSE)
      ->printMetadata(FALSE)
      ->interactive(FALSE);
  }

  /**
   * Kills all system processes that are using a particular port.
   *
   * @param string $port
   *   The port number.
   */
  public function killProcessByPort($port) {
    $this->logger->info("Killing all processes on port '$port'...");
    // This is allowed to fail.
    // @todo Replace with standardized call to Symfony Process.
    exec("command -v lsof && lsof -ti tcp:$port | xargs kill l 2>&1");
    exec("pkill -f $port 2>&1");
  }

  /**
   * Kills all system processes containing a particular string.
   *
   * @param string $name
   *   The name of the process.
   */
  public function killProcessByName($name) {
    $this->logger->info("Killing all processing containing string '$name'...");
    // This is allowed to fail.
    // @todo Replace with standardized call to Symfony Process.
    exec("ps aux | grep -i $name | grep -v grep | awk '{print $2}' | xargs kill -9 2>&1");
    // exec("ps aux | awk '/$name/ {print $2}' 2>&1 | xargs kill -9");.
  }

  /**
   * Waits until a given URL responds with a non-50x response.
   *
   * This does have a maximum timeout, defined in wait().
   *
   * @param string $url
   *   The URL to wait for.
   */
  public function waitForUrlAvailable($url) {
    $this->wait([$this, 'checkUrl'], [$url], "Waiting for response from $url...");
  }

  /**
   * Waits until a given callable returns TRUE.
   *
   * This does have a maximum timeout.
   *
   * @param callable $callable
   *   The method/function to wait for a TRUE response from.
   * @param array $args
   *   Arguments to pass to $callable.
   * @param string $message
   *   The message to display when this function is called.
   *
   * @return bool
   *   TRUE if callable returns TRUE.
   *
   * @throws \Exception
   */
  public function wait(callable $callable, array $args, $message = '') {
    $maxWait = 10 * 1000;
    $checkEvery = 1 * 1000;
    $start = microtime(TRUE) * 1000;
    $end = $start + $maxWait;

    if (!$message) {
      $method_name = is_array($callable) ? $callable[1] : $callable;
      $message = "Waiting for $method_name() to return true.";
    }

    // For some reason we can't reuse $start here.
    while (microtime(TRUE) * 1000 < $end) {
      $this->logger->info($message);
      try {
        if (call_user_func_array($callable, $args)) {
          return TRUE;
        }
      }
      catch (\Exception $e) {
        $this->logger->error($e->getMessage());
      }
      usleep($checkEvery * 1000);
    }

    throw new BltException("Timed out.");
  }

  /**
   * Checks a URL for a non-50x response.
   *
   * @param string $url
   *   The URL to check.
   *
   * @return bool
   *   TRUE if URL responded with a non-50x response.
   */
  public function checkUrl($url) {
    try {
      $client = new Client();
      $res = $client->request('GET', $url, [
        'connection_timeout' => 2,
        'timeout' => 2,
        'exceptions' => FALSE,
      ]);
      if ($res->getStatusCode() && substr($res->getStatusCode(), 0, 1) != '5') {
        return TRUE;
      }
      else {
        $this->logger->debug($res->getBody());
        return FALSE;
      }
    }
    catch (\Exception $e) {
      $this->logger->debug($e->getMessage());
    }
    return FALSE;
  }

}
