<?php

namespace Acquia\Blt\Robo\Common;

use Acquia\Blt\Robo\BltTasks;
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
   * @return \Robo\Result
   */
  public function drush($command) {
    // @todo Set to silent if verbosity is less than very verbose.
    $bin = $this->getConfigValue('composer.bin');
    $result = $this->builder->taskExec("$bin/drush $command")
      ->dir($this->getConfigValue('docroot'))
      ->printed(false)
      ->run();

    return $result;
  }

  /**
   * @param $command
   *
   * @return \Robo\Result
   */
  public function executeCommand($command) {
    return $this->builder->taskExec($command)
      ->dir($this->getConfigValue('repo.root'))
      ->printed(false)
      ->run();
  }

}
