<?php

namespace Acquia\Blt\Robo\Common;

use Acquia\Blt\Robo\Config\ConfigAwareTrait;
use Acquia\Blt\Robo\Exceptions\BltException;
use GuzzleHttp\Client;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Robo\Collection\CollectionBuilder;
use Robo\Contract\ConfigAwareInterface;
use Robo\Contract\IOAwareInterface;
use Robo\Robo;
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
   *   The command string|array.
   *   Warning: symfony/process 5.x expects an array.
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
   * @param mixed $command
   *   The command to execute, without "drush" prefix.
   *
   * @return \Robo\Common\ProcessExecutor
   *   The unexecuted process.
   */
  public function drush($command) {
    $drush_array = [];
    // @todo Set to silent if verbosity is less than very verbose.
    $drush_array[] = $this->getConfigValue('composer.bin') . DIRECTORY_SEPARATOR . "drush";
    $drush_array[] = "@" . $this->getConfigValue('drush.alias');

    // URIs do not work on remote drush aliases in Drush 9. Instead, it is
    // expected that the alias define the uri in its configuration.
    if ($this->getConfigValue('site') !== 'default') {
      $drush_array[] = '--uri=' . $this->getConfigValue('drush.uri');
    }

    if (is_array($command)) {
      $command_array = array_merge($drush_array, $command);
      $this->logger->info("Running command " . implode(" ", $command_array));
      $process_executor = $this->execute($command_array);
    }
    else {
      $drush_string = implode (" ", $drush_array);
      $this->logger->info("$drush_string $command");
      $process_executor = $this->executeShell("$drush_string $command");
    }
    return $process_executor;
  }

  /**
   * Executes a command.
   *
   * @param mixed $command
   *   The command string|array.
   *   Warning: symfony/process 5.x expects an array.
   *
   * @return \Robo\Common\ProcessExecutor
   *   The unexecuted command.
   */
  public function execute($command) {
    // Backwards compatibility check for legacy commands.
    if (!is_array($command)) {
      $this->say($command);
      $this->say(StringManipulator::stringToArrayMsg());
      $command = StringManipulator::commandConvert($command);
    }
    /** @var \Robo\Common\ProcessExecutor $process_executor */
    $process_executor = Robo::process(new Process($command));
    return $process_executor->dir($this->getConfigValue('repo.root'))
      ->printOutput(FALSE)
      ->printMetadata(FALSE)
      ->interactive(FALSE);
  }

  /**
   * Executes a shell command.
   *
   * @param string $command
   *   The shell command string.
   *
   * @return \Robo\Common\ProcessExecutor
   *   The unexecuted command.
   */
  public function executeShell($command) {
    $process_executor = Robo::process(Process::fromShellCommandline($command));
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
    // phpcs:ignore
    exec("command -v lsof && lsof -ti tcp:$port | xargs kill l 2>&1");
    // phpcs:ignore
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
    // phpcs:ignore
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
    $this->wait([$this, 'checkUrl'], [$url], "Waiting for non-50x response from $url...");
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
    $maxWait = 60 * 1000;
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
        $this->logger->info("Response code: " . $res->getStatusCode());
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
