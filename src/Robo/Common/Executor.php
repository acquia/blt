<?php

namespace Acquia\Blt\Robo\Common;

use Acquia\Blt\Robo\Config\ConfigAwareTrait;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Robo\Collection\CollectionBuilder;
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

  /** @var CollectionBuilder */
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
   * @param $command
   *
   * @return \Symfony\Component\Process\Process
   */
  public function executeDrush($command) {
    $bin = $this->getConfigValue('composer.bin');
    return $this->executeCommand("$bin/drush $command",
      $this->getConfigValue('docroot'), FALSE);
  }

  /**
   * @param string $command
   *
   * @return Process
   */
  public function executeCommand(
    $command,
    $cwd = NULL,
    $display_output = TRUE,
    $interactive = FALSE,
    $mustRun = TRUE
  ) {
    if ($this->output()->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
      $this->logger->debug("Executing command: <comment>$command</comment>");
    }

    $timeout = 10800;
    $env = [
        'COMPOSER_PROCESS_TIMEOUT' => $timeout,
      ] + $_ENV;
    $process = new Process($command, $cwd, $env, NULL, $timeout);
    $process->setTty($interactive);
    $method = $mustRun ? 'mustRun' : 'run';
    if ($display_output) {
      $process->$method(function ($type, $buffer) {
        print $buffer;
      });
    }
    else {
      $process->$method();
    }

    return $process;
  }

}
